<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Resource extends Model
{
    use HasFactory, HasUuids;

    /**
     * The connection name for the model.
     */
    protected $connection = 'libsql';

    /**
     * The table associated with the model.
     */
    protected $table = 'resources';

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'id';

    /**
     * The data type of the primary key ID.
     */
    protected $keyType = 'string';

    /**
     * Indicates if the model's ID is auto-incrementing.
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'course_id',
        'title',
        'content',
        'embedding',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'embedding' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the course that owns the resource.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Scope to search resources by vector similarity.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $embedding The query embedding vector
     * @param int $limit Maximum number of results
     * @param float $threshold Minimum similarity threshold (0-1)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSimilarTo($query, array $embedding, int $limit = 10, float $threshold = 0.7)
    {
        try {
            return $query
                ->selectRaw('*, VECTOR_COSINE_SIMILARITY(embedding, ?) as similarity', [json_encode($embedding)])
                ->whereRaw('VECTOR_COSINE_SIMILARITY(embedding, ?) > ?', [json_encode($embedding), $threshold])
                ->orderByDesc('similarity')
                ->limit($limit);
        } catch (\Exception $e) {
            // Fallback: return empty results if vector operations aren't available
            return $query->whereRaw('1 = 0');
        }
    }

    /**
     * Scope to filter resources by course.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $courseId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForCourse($query, int $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    /**
     * Scope to search resources by title or content.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $searchTerm
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, string $searchTerm)
    {
        return $query->where(function ($q) use ($searchTerm) {
            $q->where('title', 'LIKE', "%{$searchTerm}%")
              ->orWhere('content', 'LIKE', "%{$searchTerm}%");
        });
    }

    /**
     * Get the embedding as a normalized array.
     * 
     * @return array|null
     */
    public function getEmbeddingArray(): ?array
    {
        return $this->embedding;
    }

    /**
     * Set the embedding from an array.
     * 
     * @param array $embedding
     * @return void
     */
    public function setEmbeddingArray(array $embedding): void
    {
        $this->embedding = $embedding;
    }

    /**
     * Check if the resource has an embedding.
     * 
     * @return bool
     */
    public function hasEmbedding(): bool
    {
        return !empty($this->embedding) && is_array($this->embedding);
    }

    /**
     * Get the embedding dimension.
     * 
     * @return int|null
     */
    public function getEmbeddingDimension(): ?int
    {
        return $this->hasEmbedding() ? count($this->embedding) : null;
    }
}
