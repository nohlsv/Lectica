<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'aliases'];

    protected $casts = [
        'aliases' => 'array',
    ];

    public function files(): BelongsToMany
    {
        return $this->belongsToMany(File::class, 'file_tag', 'tag_id', 'file_id');
    }

    /**
     * Search for tags by name or aliases
     */
    public static function searchByNameOrAlias(string $search)
    {
        return static::where('name', 'LIKE', "%{$search}%")
            ->orWhereJsonContains('aliases', $search)
            ->orWhere(function ($query) use ($search) {
                $query->whereRaw('JSON_UNQUOTE(JSON_EXTRACT(aliases, "$[*]")) LIKE ?', ["%{$search}%"]);
            });
    }

    /**
     * Get all searchable terms (name + aliases) for this tag
     */
    public function getSearchableTerms(): array
    {
        $terms = [$this->name];

        if ($this->aliases && is_array($this->aliases)) {
            $terms = array_merge($terms, $this->aliases);
        }

        return array_unique($terms);
    }

    /**
     * Check if a search term matches this tag's name or aliases
     */
    public function matchesSearchTerm(string $term): bool
    {
        $searchableTerms = $this->getSearchableTerms();

        foreach ($searchableTerms as $searchableTerm) {
            if (stripos($searchableTerm, $term) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add an alias to this tag
     */
    public function addAlias(string $alias): self
    {
        $aliases = $this->aliases ?? [];

        if (!in_array($alias, $aliases)) {
            $aliases[] = $alias;
            $this->aliases = $aliases;
        }

        return $this;
    }

    /**
     * Remove an alias from this tag
     */
    public function removeAlias(string $alias): self
    {
        $aliases = $this->aliases ?? [];

        $this->aliases = array_values(array_filter($aliases, function ($item) use ($alias) {
            return $item !== $alias;
        }));

        return $this;
    }

    /**
     * Get a formatted display of all names and aliases
     */
    public function getDisplayNames(): string
    {
        $names = [$this->name];

        if ($this->aliases && is_array($this->aliases)) {
            $names = array_merge($names, $this->aliases);
        }

        return implode(', ', $names);
    }
}
