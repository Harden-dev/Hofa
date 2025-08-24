<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Enfiler extends Model
{
    use HasUlids;

    protected $table = 'enfilers';

    protected $fillable = [
        'slug',
        'type',
        'name',
        'bossName',
        'donationType',
        'phone',
        'email',
        'motivation',
        'is_active',
        'approved_at',
        'rejected_at',
        'rejection_reason',
        'is_approved',
        'is_rejected',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'is_approved' => 'boolean',
        'is_rejected' => 'boolean',
    ];

    // Scopes pour filtrer les dons
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('is_active', false);
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

    public function scopeByDonationType(Builder $query, string $donationType): Builder
    {
        return $query->where('donationType', $donationType);
    }

    public function scopeWithMotivation(Builder $query): Builder
    {
        return $query->whereNotNull('motivation');
    }

    public function scopeWithoutMotivation(Builder $query): Builder
    {
        return $query->whereNull('motivation');
    }

    // Accesseurs pour obtenir des valeurs formatées
    public function getFullNameAttribute(): string
    {
        return $this->name ?? 'N/A';
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->type === 'company') {
            return $this->name ?? 'N/A';
        }

        return $this->name ?? 'N/A';
    }

    public function getStatusTextAttribute(): string
    {
        return $this->is_active ? 'Actif' : 'Inactif';
    }

    public function getDonationTypeTextAttribute(): string
    {
        return ucfirst($this->donationType ?? 'N/A');
    }

    public function getTypeTextAttribute(): string
    {
        return match($this->type) {
            'individual' => 'Individuel',
            'company' => 'Entreprise',
            default => 'N/A'
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

    public function setDonationTypeAttribute($value): void
    {
        $this->attributes['donationType'] = ucfirst(trim($value));
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

    public function isIndividual(): bool
    {
        return $this->type === 'individual';
    }

    public function isCompany(): bool
    {
        return $this->type === 'company';
    }

    public function hasMotivation(): bool
    {
        return !is_null($this->motivation);
    }

    public function hasBossName(): bool
    {
        return !is_null($this->bossName);
    }
}
