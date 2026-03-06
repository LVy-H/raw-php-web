<?php

namespace App\Models;

class SubmissionModel extends BaseModel
{
    public function findByPracticeAndStudent(int $practiceId, int $studentId): ?array
    {
        return $this->db->query(
            'SELECT id, practice_id, student_id, file_name, stored_name, submitted_at
             FROM submissions
             WHERE practice_id = :practice_id AND student_id = :student_id',
            [
                'practice_id' => $practiceId,
                'student_id' => $studentId,
            ]
        )->fetch() ?: null;
    }

    public function findById(int $id): ?array
    {
        return $this->db->query(
            'SELECT id, practice_id, student_id, file_name, stored_name, submitted_at
             FROM submissions
             WHERE id = :id',
            ['id' => $id]
        )->fetch() ?: null;
    }

    public function upsert(int $practiceId, int $studentId, string $fileName, string $storedName): void
    {
        $this->db->query(
            'INSERT INTO submissions (practice_id, student_id, file_name, stored_name)
             VALUES (:practice_id, :student_id, :file_name, :stored_name)
             ON CONFLICT(practice_id, student_id)
             DO UPDATE SET
                file_name = excluded.file_name,
                stored_name = excluded.stored_name,
                submitted_at = CURRENT_TIMESTAMP',
            [
                'practice_id' => $practiceId,
                'student_id' => $studentId,
                'file_name' => $fileName,
                'stored_name' => $storedName,
            ]
        );
    }

    public function forPractice(int $practiceId): array
    {
        return $this->db->query(
            'SELECT s.id, s.file_name, s.submitted_at,
                    u.id AS student_id, u.name AS student_name, u.username AS student_username
             FROM submissions s
             INNER JOIN users u ON u.id = s.student_id
             WHERE s.practice_id = :practice_id
             ORDER BY s.submitted_at DESC',
            ['practice_id' => $practiceId]
        )->fetchAll();
    }

    public function forStudent(int $studentId): array
    {
        return $this->db->query(
            'SELECT id, practice_id, file_name, submitted_at
             FROM submissions
             WHERE student_id = :student_id',
            ['student_id' => $studentId]
        )->fetchAll();
    }
}
