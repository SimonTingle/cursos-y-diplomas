<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UserImportService
{
    public function import(UploadedFile $file): ImportResult
    {
        $rows = $this->parseFile($file);
        $successCount = 0;
        $failureCount = 0;
        $errors = [];
        $createdUsers = [];

        foreach ($rows as $rowNumber => $row) {
            try {
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                // Validate row
                $this->validateRow($row, $rowNumber);

                // Generate temporary password
                $password = Str::password(12);

                // Create user
                $user = User::create([
                    'name'     => $row['name'],
                    'email'    => $row['email'],
                    'password' => Hash::make($password),
                    'role'     => $row['role'] ?? 'instructor',
                    'phone'    => $row['phone'] ?? null,
                    'title'    => $row['title'] ?? null,
                    'bio'      => $row['bio'] ?? null,
                    'is_active' => $row['is_active'] ?? true,
                ]);

                // Store password for email notification
                $createdUsers[] = [
                    'user'     => $user,
                    'password' => $password,
                ];

                $successCount++;

            } catch (ValidationException $e) {
                $failureCount++;
                $errors[] = [
                    'row'    => $rowNumber,
                    'errors' => $e->messages(),
                ];
            } catch (\Exception $e) {
                $failureCount++;
                $errors[] = [
                    'row'    => $rowNumber,
                    'errors' => ['general' => [$e->getMessage()]],
                ];
            }
        }

        return new ImportResult(
            successCount: $successCount,
            failureCount: $failureCount,
            errors: $errors,
            createdUsers: $createdUsers,
            filename: $file->getClientOriginalName(),
            totalRows: count($rows),
        );
    }

    private function parseFile(UploadedFile $file): array
    {
        $extension = $file->getClientOriginalExtension();

        if ($extension === 'csv') {
            return $this->parseCSV($file);
        } elseif ($extension === 'json') {
            return $this->parseJSON($file);
        }

        throw new \InvalidArgumentException('Unsupported file format. Only CSV and JSON are supported.');
    }

    private function parseCSV(UploadedFile $file): array
    {
        $rows = [];
        $handle = fopen($file->getRealPath(), 'r');

        if (!$handle) {
            throw new \RuntimeException('Could not open CSV file');
        }

        // Read header row
        $header = fgetcsv($handle);
        $rowNumber = 2;

        while (($row = fgetcsv($handle)) !== false) {
            $data = [];
            foreach ($header as $index => $column) {
                $data[strtolower($column)] = $row[$index] ?? null;
            }
            $rows[$rowNumber] = $data;
            $rowNumber++;
        }

        fclose($handle);
        return $rows;
    }

    private function parseJSON(UploadedFile $file): array
    {
        $content = file_get_contents($file->getRealPath());
        $data = json_decode($content, true);

        if (!is_array($data)) {
            throw new \RuntimeException('Invalid JSON format. Expected array of user objects.');
        }

        $rows = [];
        foreach ($data as $rowNumber => $row) {
            $rows[$rowNumber + 2] = array_map('strtolower', array_combine(
                array_keys($row),
                array_values($row)
            ));
        }

        return $rows;
    }

    private function validateRow(array $row, int $rowNumber): void
    {
        $errors = [];

        // Required fields
        if (empty($row['name'])) {
            $errors['name'][] = "Name is required";
        }
        if (empty($row['email'])) {
            $errors['email'][] = "Email is required";
        }
        if (!empty($row['email']) && !filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'][] = "Email must be a valid email address";
        }

        // Email uniqueness
        if (!empty($row['email']) && User::where('email', $row['email'])->exists()) {
            $errors['email'][] = "Email already exists";
        }

        // Role validation
        if (!empty($row['role']) && !in_array($row['role'], ['admin', 'instructor'])) {
            $errors['role'][] = "Role must be either 'admin' or 'instructor'";
        }

        // Phone validation
        if (!empty($row['phone']) && strlen($row['phone']) > 20) {
            $errors['phone'][] = "Phone must not exceed 20 characters";
        }

        // Title validation
        if (!empty($row['title']) && strlen($row['title']) > 255) {
            $errors['title'][] = "Title must not exceed 255 characters";
        }

        // Bio validation
        if (!empty($row['bio']) && strlen($row['bio']) > 1000) {
            $errors['bio'][] = "Bio must not exceed 1000 characters";
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }
}

class ImportResult
{
    public function __construct(
        public int $successCount,
        public int $failureCount,
        public array $errors,
        public array $createdUsers,
        public string $filename,
        public int $totalRows,
    ) {}

    public function hasErrors(): bool
    {
        return $this->failureCount > 0;
    }

    public function getErrorSummary(): string
    {
        $lines = ["Import completed with {$this->failureCount} error(s):"];
        foreach ($this->errors as $error) {
            $lines[] = "Row {$error['row']}: " . implode(', ', array_merge(...array_values($error['errors'])));
        }
        return implode("\n", $lines);
    }
}
