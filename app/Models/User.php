<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements CanResetPassword
{
    /** @use HasFactory<UserFactory> */
    use CanResetPasswordTrait, HasFactory, Notifiable;

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function productReviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function favoriteProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_user_favorites')
            ->withTimestamps();
    }

    public function hasCompletedPurchaseOf(Product $product): bool
    {
        return $this->orders()
            ->where('status', 'completed')
            ->whereHas('items', fn (Builder $query) => $query->whereBelongsTo($product))
            ->exists();
    }

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
        ];
    }
}
