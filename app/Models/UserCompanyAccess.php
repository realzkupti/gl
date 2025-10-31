<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasUserTracking;

class UserCompanyAccess extends Model
{
    use HasUserTracking;

    protected $connection = 'pgsql';
    protected $table = 'sys_user_company_access';

    protected $fillable = [
        'user_id',
        'company_id',
    ];

    /**
     * User relationship
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Company relationship
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * Check if user has access to company
     */
    public static function hasAccess(int $userId, int $companyId): bool
    {
        return static::where('user_id', $userId)
            ->where('company_id', $companyId)
            ->exists();
    }

    /**
     * Grant access to company for user
     */
    public static function grantAccess(int $userId, int $companyId): void
    {
        static::firstOrCreate([
            'user_id' => $userId,
            'company_id' => $companyId,
        ]);
    }

    /**
     * Revoke access to company for user
     */
    public static function revokeAccess(int $userId, int $companyId): void
    {
        static::where('user_id', $userId)
            ->where('company_id', $companyId)
            ->delete();
    }

    /**
     * Get all companies user has access to
     */
    public static function getCompaniesForUser(int $userId)
    {
        return static::where('user_id', $userId)
            ->with('company')
            ->get()
            ->pluck('company');
    }

    /**
     * Get all users who have access to company
     */
    public static function getUsersForCompany(int $companyId)
    {
        return static::where('company_id', $companyId)
            ->with('user')
            ->get()
            ->pluck('user');
    }
}
