<?php

namespace App\Models;

class PracticeModel extends BaseModel
{
    public function allWithUploader(): array
    {
        $stmt = $this->db->query(
            'SELECT p.id, p.title, p.description, p.file_name, p.stored_name, p.created_at,
                    u.name AS uploader_name
             FROM practices p
             INNER JOIN users u ON u.id = p.uploaded_by
             ORDER BY p.id DESC'
        );

        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->query(
            'SELECT p.id, p.title, p.description, p.file_name, p.stored_name, p.created_at,
                    u.name AS uploader_name
             FROM practices p
             INNER JOIN users u ON u.id = p.uploaded_by
             WHERE p.id = :id',
            ['id' => $id]
        );

        return $stmt->fetch() ?: null;
    }

    public function create(array $data): void
    {
        $this->db->query(
            'INSERT INTO practices (title, description, file_name, stored_name, uploaded_by)
             VALUES (:title, :description, :file_name, :stored_name, :uploaded_by)',
            [
                'title' => $data['title'],
                'description' => $data['description'],
                'file_name' => $data['file_name'],
                'stored_name' => $data['stored_name'],
                'uploaded_by' => $data['uploaded_by'],
            ]
        );
    }
}
