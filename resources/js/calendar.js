import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import esLocale from '@fullcalendar/core/locales/es';

// FullCalendar locale bundles, keyed by primary language code.
const fcLocales = { es: esLocale };

const token = document
    .querySelector('meta[name="csrf-token"]')
    ?.getAttribute('content');

const json = async (url, options = {}) => {
    const res = await fetch(url, {
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': token,
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
        ...options,
    });
    if (!res.ok) {
        const body = await res.json().catch(() => ({}));
        throw Object.assign(new Error('Request failed'), { status: res.status, body });
    }
    return res.status === 204 ? null : res.json();
};

const toLocalInput = (date) => {
    if (!date) return '';
    const d = new Date(date);
    const pad = (n) => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
};

/**
 * Alpine component powering the Instructors Calendar page.
 */
export default function calendarApp(config) {
    return {
        calendar: null,
        modalOpen: false,
        saving: false,
        deleting: false,
        errors: {},
        instructorFilter: '',
        form: blankForm(),

        get isAdmin() {
            return !!config.isAdmin;
        },

        get i18n() {
            return config.i18n ?? {};
        },

        init() {
            const admin = this.isAdmin;
            const localeCode = (config.locale ?? 'en').slice(0, 2);
            this.calendar = new Calendar(this.$refs.calendar, {
                plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
                locale: fcLocales[localeCode] ?? undefined,
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay',
                },
                height: '100%',
                expandRows: true,
                nowIndicator: true,
                navLinks: true,
                dayMaxEvents: true,
                selectable: admin,
                editable: admin,
                eventTimeFormat: { hour: '2-digit', minute: '2-digit', meridiem: 'short' },
                events: (info, success, failure) => {
                    const params = new URLSearchParams({
                        start: info.startStr,
                        end: info.endStr,
                    });
                    if (this.instructorFilter) params.set('instructor_id', this.instructorFilter);
                    json(`${config.eventsUrl}?${params.toString()}`)
                        .then(success)
                        .catch(failure);
                },
                select: (info) => admin && this.openCreate(info.start, info.end, info.allDay),
                dateClick: (info) => admin && this.openCreate(info.date, null, info.allDay),
                eventClick: (info) => admin && this.openEdit(info.event),
                eventDrop: (info) => this.persistDrag(info),
                eventResize: (info) => this.persistDrag(info),
            });
            this.calendar.render();
        },

        refilter() {
            this.calendar.refetchEvents();
        },

        openCreate(start, end, allDay) {
            this.errors = {};
            this.form = blankForm();
            this.form.start_at = toLocalInput(start);
            this.form.end_at = end ? toLocalInput(end) : '';
            this.form.all_day = !!allDay;
            this.form.instructor_id = this.instructorFilter || '';
            this.modalOpen = true;
        },

        openEdit(event) {
            this.errors = {};
            const p = event.extendedProps;
            this.form = {
                id: event.id,
                title: event.title,
                description: p.description ?? '',
                instructor_id: p.instructor_id ?? '',
                start_at: toLocalInput(event.start),
                end_at: event.end ? toLocalInput(event.end) : '',
                all_day: event.allDay,
                location: p.location ?? '',
                color: event.backgroundColor ?? '',
                status: p.status ?? 'scheduled',
            };
            this.modalOpen = true;
        },

        async save() {
            this.saving = true;
            this.errors = {};
            const payload = { ...this.form };
            const isUpdate = !!payload.id;
            const url = isUpdate ? `${config.eventsUrl}/${payload.id}` : config.eventsUrl;
            try {
                await json(url, {
                    method: isUpdate ? 'PUT' : 'POST',
                    body: JSON.stringify(payload),
                });
                this.modalOpen = false;
                this.calendar.refetchEvents();
            } catch (e) {
                if (e.status === 422) this.errors = e.body.errors ?? {};
                else alert(this.i18n.saveError ?? 'Could not save the session.');
            } finally {
                this.saving = false;
            }
        },

        async remove() {
            if (!this.form.id || !confirm(this.i18n.confirmDelete ?? 'Delete this session?')) return;
            this.deleting = true;
            try {
                await json(`${config.eventsUrl}/${this.form.id}`, { method: 'DELETE' });
                this.modalOpen = false;
                this.calendar.refetchEvents();
            } catch (e) {
                alert(this.i18n.deleteError ?? 'Could not delete the session.');
            } finally {
                this.deleting = false;
            }
        },

        async persistDrag(info) {
            try {
                await json(`${config.eventsUrl}/${info.event.id}`, {
                    method: 'PUT',
                    body: JSON.stringify({
                        title: info.event.title,
                        instructor_id: info.event.extendedProps.instructor_id || null,
                        start_at: toLocalInput(info.event.start),
                        end_at: info.event.end ? toLocalInput(info.event.end) : null,
                        all_day: info.event.allDay,
                        location: info.event.extendedProps.location || null,
                        status: info.event.extendedProps.status || 'scheduled',
                    }),
                });
            } catch (e) {
                info.revert();
                alert(this.i18n.moveError ?? 'Could not move the session.');
            }
        },
    };
}

function blankForm() {
    return {
        id: null,
        title: '',
        description: '',
        instructor_id: '',
        start_at: '',
        end_at: '',
        all_day: false,
        location: '',
        color: '',
        status: 'scheduled',
    };
}
