<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\File;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'program_id',
        'year_of_study',
        'user_role',
        'level',
        'experience',
        'experience_to_next_level',
        'verification_document_path',
        'verification_status',
        'verification_notes',
        'document_uploaded_at',
        'verified_at',
        'verified_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'level' => 'integer',
            'experience' => 'integer',
            'experience_to_next_level' => 'integer',
            'document_uploaded_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    /**
     * Get the program that the user belongs to.
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Get the files that belong to the user.
     */
    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }

    /**
     * Get the files that the user has starred.
     */
    public function starredFiles(): BelongsToMany
    {
        return $this->belongsToMany(File::class, 'file_stars')
            ->withTimestamps();
    }

    /**
     * Check if a user has starred a specific file
     */
    public function hasStarred(File $file): bool
    {
        return $this->starredFiles()->where('file_id', $file->id)->exists();
    }

    /**
     * Check if the user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->is_admin === true || $this->user_role === 'admin';
    }

    /**
     * Get the battles for this user.
     */
    public function battles(): HasMany
    {
        return $this->hasMany(Battle::class);
    }

    /**
     * Get the quests assigned to this user.
     */
    public function quests(): BelongsToMany
    {
        return $this->belongsToMany(Quest::class, 'user_quests')
                    ->withPivot(['progress', 'target', 'is_completed', 'completed_at', 'assigned_date'])
                    ->withTimestamps();
    }

    /**
     * Get active (incomplete) quests for this user.
     */
    public function activeQuests(): BelongsToMany
    {
        return $this->quests()->wherePivot('is_completed', false);
    }

    /**
     * Get completed quests for this user.
     */
    public function completedQuests(): BelongsToMany
    {
        return $this->quests()->wherePivot('is_completed', true);
    }

    /**
     * Get today's quests for this user.
     */
    public function todaysQuests(): BelongsToMany
    {
        return $this->quests()->wherePivot('assigned_date', today());
    }

    /**
     * Add experience and handle level ups.
     */
    public function addExperience(int $amount): void
    {
        $this->experience += $amount;

        while ($this->experience >= $this->experience_to_next_level) {
            $this->levelUp();
        }

        $this->save();
    }

    /**
     * Level up the user.
     */
    private function levelUp(): void
    {
        $this->experience -= $this->experience_to_next_level;
        $this->level++;

        // Calculate XP needed for next level (increases by 20% each level)
        $this->experience_to_next_level = (int) round($this->experience_to_next_level * 1.2);
    }

    /**
     * Get experience progress percentage for current level.
     */
    public function getExperienceProgress(): float
    {
        if ($this->experience_to_next_level == 0) {
            return 100;
        }

        return round(($this->experience / $this->experience_to_next_level) * 100, 1);
    }

    /**
     * Get the user's level badge color.
     */
    public function getLevelBadgeColor(): string
    {
        return match(true) {
            $this->level >= 50 => 'purple',
            $this->level >= 25 => 'red',
            $this->level >= 10 => 'yellow',
            $this->level >= 5 => 'blue',
            default => 'green'
        };
    }

    /**
     * Get collections owned by this user.
     */
    public function collections(): HasMany
    {
        return $this->hasMany(Collection::class);
    }

    /**
     * Get collections favorited by this user.
     */
    public function favoritedCollections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class, 'collection_favorites')
            ->withTimestamps();
    }

    /**
     * Get original collections created by this user (not copies).
     */
    public function originalCollections(): HasMany
    {
        return $this->hasMany(Collection::class)->where('is_original', true);
    }

    /**
     * Get collections copied by this user.
     */
    public function copiedCollections(): HasMany
    {
        return $this->hasMany(Collection::class)->where('is_original', false);
    }

    /**
     * Get the admin who verified this user.
     */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Check if the user needs to upload verification document.
     */
    public function needsDocumentUpload(): bool
    {
        return is_null($this->verification_document_path) && 
               $this->verification_status === 'pending';
    }

    /**
     * Check if the user is verified and can access the full system.
     */
    public function isFullyVerified(): bool
    {
        return $this->hasVerifiedEmail() && 
               $this->verification_status === 'approved';
    }

    /**
     * Check if the user's verification is pending admin approval.
     */
    public function hasDocumentPendingApproval(): bool
    {
        return !is_null($this->verification_document_path) && 
               $this->verification_status === 'pending';
    }

    /**
     * Check if the user's verification was rejected.
     */
    public function isVerificationRejected(): bool
    {
        return $this->verification_status === 'rejected';
    }

    /**
     * Get the URL for the verification document.
     */
    public function getVerificationDocumentUrlAttribute(): ?string
    {
        if (!$this->verification_document_path) {
            return null;
        }
        
        return route('verification.document', $this->id);
    }
}
