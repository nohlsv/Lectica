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
        $search = trim($search);
        if (empty($search)) {
            return static::query();
        }

        return static::where('name', 'LIKE', "%{$search}%")
            ->orWhereJsonContains('aliases', $search)
            ->orWhere(function ($query) use ($search) {
                // Fixed SQL injection vulnerability by using parameter binding
                $query->whereRaw('JSON_SEARCH(aliases, "one", ?) IS NOT NULL', ["%{$search}%"]);
            });
    }

    /**
     * Get all searchable terms (name + aliases) for this tag
     */
    public function getSearchableTerms(): array
    {
        $terms = [$this->name];

        if ($this->aliases && is_array($this->aliases)) {
            // Filter out empty/null aliases
            $validAliases = array_filter($this->aliases, function($alias) {
                return !empty(trim($alias));
            });
            $terms = array_merge($terms, $validAliases);
        }

        return array_unique(array_filter($terms));
    }

    /**
     * Check if a search term matches this tag's name or aliases
     */
    public function matchesSearchTerm(string $term): bool
    {
        $term = trim($term);
        if (empty($term)) {
            return false;
        }

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
        $alias = trim($alias);
        if (empty($alias)) {
            return $this;
        }

        $aliases = $this->aliases ?? [];

        // Case-insensitive check to prevent duplicates
        $existingAliases = array_map('tolower', $aliases);
        if (!in_array(strtolower($alias), $existingAliases) && strtolower($alias) !== strtolower($this->name)) {
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
        $alias = trim($alias);
        if (empty($alias)) {
            return $this;
        }

        $aliases = $this->aliases ?? [];

        // Case-insensitive removal
        $this->aliases = array_values(array_filter($aliases, function ($item) use ($alias) {
            return strcasecmp($item, $alias) !== 0;
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
