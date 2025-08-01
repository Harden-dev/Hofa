<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Member extends Model
{
    use HasUlids;

    protected $table = 'members';

    protected $fillable = [
        'slug',
        'type',
        'name',
        'bossName',
        'phone',
        'email',
        'gender',
        'nationality',
        'matrimonial',
        'is_volunteer',
        'is_active',
        'is_approved',
        'is_rejected',
        'habit',
        'bio',
        'job',
        'volunteer',
        'origin',
        'web',
        'activity',
        'approved_at',
        'rejected_at',
        'rejection_reason',
    ];

    protected $casts = [
        'is_volunteer' => 'boolean',
        'is_active' => 'boolean',
        'is_approved' => 'boolean',
        'is_rejected' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    // Scopes pour filtrer les membres
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('is_active', false);
    }

    public function scopeVolunteers(Builder $query): Builder
    {
        return $query->where('is_volunteer', true);
    }

    public function scopeNonVolunteers(Builder $query): Builder
    {
        return $query->where('is_volunteer', false);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeIndividuals(Builder $query): Builder
    {
        return $query->byType('individual');
    }

    public function scopeCompanies(Builder $query): Builder
    {
        return $query->byType('company');
    }

    public function scopeByGender(Builder $query, string $gender): Builder
    {
        return $query->where('gender', $gender);
    }

    public function scopeByNationality(Builder $query, string $nationality): Builder
    {
        return $query->where('nationality', $nationality);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('is_approved', true);
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('is_rejected', true);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('is_approved', false)->where('is_rejected', false);
    }

    // Accesseurs pour obtenir des valeurs formatées
    public function getFullNameAttribute(): string
    {
        if ($this->type === 'company' && empty($this->name)) {
            return $this->bossName ?? 'Entreprise sans nom';
        }
        return $this->name ?? 'N/A';
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->type === 'company') {
            return $this->bossName ?? 'N/A';
        }

        return $this->name ?? 'N/A';
    }

    public function getStatusTextAttribute(): string
    {
        return $this->is_active ? 'Actif' : 'Inactif';
    }

    public function getVolunteerStatusTextAttribute(): string
    {
        return $this->is_volunteer ? 'Bénévole' : 'Non-Bénévole';
    }

    public function getApprovalStatusAttribute(): string
    {
        if ($this->is_approved) {
            return 'approved';
        } elseif ($this->is_rejected) {
            return 'rejected';
        } else {
            return 'pending';
        }
    }

    public function getApprovalStatusTextAttribute(): string
    {
        return match($this->approval_status) {
            'approved' => 'Approuvé',
            'rejected' => 'Rejeté',
            default => 'En attente'
        };
    }

    // Mutateurs pour formater les données avant sauvegarde
    public function setEmailAttribute($value): void
    {
        $this->attributes['email'] = strtolower(trim($value));
    }

    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = ucwords(trim($value));
    }

    public function setBossNameAttribute($value): void
    {
        $this->attributes['bossName'] = ucwords(trim($value));
    }

    // Méthodes utilitaires
    public function activate(): bool
    {
        return $this->update(['is_active' => true]);
    }

    public function deactivate(): bool
    {
        return $this->update(['is_active' => false]);
    }

    public function toggleActive(): bool
    {
        return $this->update(['is_active' => !$this->is_active]);
    }

    public function makeVolunteer(): bool
    {
        return $this->update(['is_volunteer' => true]);
    }

    public function removeVolunteer(): bool
    {
        return $this->update(['is_volunteer' => false]);
    }

    public function toggleVolunteer(): bool
    {
        return $this->update(['is_volunteer' => !$this->is_volunteer]);
    }

    public function isIndividual(): bool
    {
        return $this->type === 'individual';
    }

    public function isCompany(): bool
    {
        return $this->type === 'company';
    }
}
