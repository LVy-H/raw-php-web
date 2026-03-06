<?php

namespace App\Models;

class NoteModel extends BaseModel
{
    public function listVisibleForProfile(int $profileId, int $viewerId): array
    {
        $params = ['profile_id' => $profileId];
        $sql = 'SELECT n.id, n.profile_user_id, n.writer_user_id, n.content, n.created_at, n.updated_at,
                       w.name AS writer_name, w.username AS writer_username
                FROM notes n
                INNER JOIN users w ON w.id = n.writer_user_id
                WHERE n.profile_user_id = :profile_id';

        if ($profileId !== $viewerId) {
            $sql .= ' AND n.writer_user_id = :viewer_id';
            $params['viewer_id'] = $viewerId;
        }

        $sql .= ' ORDER BY n.updated_at DESC, n.id DESC';

        return $this->db->query($sql, $params)->fetchAll();
    }

    public function create(int $profileId, int $writerId, string $content): void
    {
        $this->db->query(
            'INSERT INTO notes (profile_user_id, writer_user_id, content)
             VALUES (:profile_user_id, :writer_user_id, :content)',
            [
                'profile_user_id' => $profileId,
                'writer_user_id' => $writerId,
                'content' => $content,
            ]
        );
    }

    public function findById(int $id): ?array
    {
        return $this->db->query(
            'SELECT id, profile_user_id, writer_user_id, content FROM notes WHERE id = :id',
            ['id' => $id]
        )->fetch() ?: null;
    }

    public function updateContent(int $id, int $writerId, string $content): void
    {
        $this->db->query(
            'UPDATE notes
             SET content = :content, updated_at = CURRENT_TIMESTAMP
             WHERE id = :id AND writer_user_id = :writer_user_id',
            [
                'id' => $id,
                'writer_user_id' => $writerId,
                'content' => $content,
            ]
        );
    }

    public function deleteByIdAndWriter(int $id, int $writerId): void
    {
        $this->db->query(
            'DELETE FROM notes WHERE id = :id AND writer_user_id = :writer_user_id',
            [
                'id' => $id,
                'writer_user_id' => $writerId,
            ]
        );
    }
}
