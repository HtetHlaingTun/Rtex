<?php


namespace App\Models;

use App\Models\Alert;
use App\Models\Notification;
use App\Models\Watchlist;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'permissions',
        'profile_photo',
        'phone',
        'department',
        'is_active',
        'last_login_at',
        'last_login_ip',
        'last_login_user_agent',
        'notify_on_verification',
        'notify_on_new_entry',
        'notify_on_rejection',
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'password_changed_at',
        'login_count',
        'entries_created_count',
        'entries_verified_count',
        'created_by',
        'updated_by',


    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'permissions' => 'array',
        'role' => 'string',
        'two_factor_recovery_codes' => 'array',
        'is_active' => 'boolean',
        'two_factor_enabled' => 'boolean',
        'notify_on_verification' => 'boolean',
        'notify_on_new_entry' => 'boolean',
        'notify_on_rejection' => 'boolean',
        'last_login_at' => 'datetime',
        'password_changed_at' => 'datetime',
        'login_count' => 'integer',
        'entries_created_count' => 'integer',
        'entries_verified_count' => 'integer'
    ];

    /**
     * Relationships
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function createdCurrencies()
    {
        return $this->hasMany(Currency::class, 'created_by');
    }

    public function updatedCurrencies()
    {
        return $this->hasMany(Currency::class, 'updated_by');
    }

    public function createdGoldTypes()
    {
        return $this->hasMany(GoldType::class, 'created_by');
    }

    public function createdExchangeRates()
    {
        return $this->hasMany(ExchangeRate::class, 'created_by');
    }

    public function verifiedExchangeRates()
    {
        return $this->hasMany(ExchangeRate::class, 'verified_by');
    }

    public function createdGoldPrices()
    {
        return $this->hasMany(GoldPrice::class, 'created_by');
    }

    public function verifiedGoldPrices()
    {
        return $this->hasMany(GoldPrice::class, 'verified_by');
    }

    // public function createdMarketUpdates()
    // {
    //     return $this->hasMany(MarketUpdate::class, 'created_by');
    // }

    // public function updatedMarketUpdates()
    // {
    //     return $this->hasMany(MarketUpdate::class, 'updated_by');
    // }

    public function priceHistories()
    {
        return $this->hasMany(PriceHistory::class, 'user_id');
    }

    public function verificationQueueSubmissions()
    {
        return $this->hasMany(VerificationQueue::class, 'submitted_by');
    }

    public function verificationQueueReviews()
    {
        return $this->hasMany(VerificationQueue::class, 'reviewed_by');
    }

    /**
     * Accessors
     */
    public function getIsSuperAdminAttribute(): bool
    {
        return $this->role === 'super_admin';
    }

    public function getIsAdminAttribute(): bool
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    public function getIsViewerAttribute(): bool
    {
        return in_array($this->role, ['viewer']);
    }


    public function getCanEditAttribute(): bool
    {
        return in_array($this->role, ['super_admin', 'admin', 'editor']);
    }

    public function getCanVerifyAttribute(): bool
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    public function getProfilePhotoUrlAttribute(): ?string
    {
        return $this->profile_photo
            ? asset('storage/' . $this->profile_photo)
            : null;
    }

    public function getRoleLabelAttribute(): string
    {
        return ucwords(str_replace('_', ' ', $this->role));
    }

    public function getStatusBadgeAttribute(): string
    {
        return $this->is_active
            ? '<span class="px-2 py-1 text-xs font-medium text-green-800 bg-green-100 rounded-full">Active</span>'
            : '<span class="px-2 py-1 text-xs font-medium text-red-800 bg-red-100 rounded-full">Inactive</span>';
    }

    /**
     * Methods
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->is_super_admin) {
            return true;
        }

        $permissions = $this->permissions ?? [];
        return in_array($permission, $permissions);
    }

    public function hasAnyPermission(array $permissions): bool
    {
        if ($this->is_super_admin) {
            return true;
        }

        $userPermissions = $this->permissions ?? [];
        return !empty(array_intersect($permissions, $userPermissions));
    }

    public function hasAllPermissions(array $permissions): bool
    {
        if ($this->is_super_admin) {
            return true;
        }

        $userPermissions = $this->permissions ?? [];
        return !array_diff($permissions, $userPermissions);
    }

    public function recordLogin(Request $request): void
    {
        $this->increment('login_count');
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
            'last_login_user_agent' => $request->userAgent()
        ]);
    }

    public function incrementCreatedCount(): void
    {
        $this->increment('entries_created_count');
    }

    public function incrementVerifiedCount(): void
    {
        $this->increment('entries_verified_count');
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function isPasswordExpired(int $days = 90): bool
    {
        if (!$this->password_changed_at) {
            return true;
        }

        return $this->password_changed_at->addDays($days)->isPast();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    public function scopeAdmins($query)
    {
        return $query->whereIn('role', ['super_admin', 'admin']);
    }

    public function scopeEditors($query)
    {
        return $query->whereIn('role', ['super_admin', 'admin', 'editor']);
    }

    public function scopeRecentlyActive($query, $days = 7)
    {
        return $query->where('last_login_at', '>=', now()->subDays($days));
    }

    // Relatinon 
    public function assets()
    {
        return $this->hasMany(UserAsset::class);
    }

    public function watchlist()
    {
        return $this->hasMany(Watchlist::class);
    }

    public function alerts()
    {
        return $this->hasMany(Alert::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}
