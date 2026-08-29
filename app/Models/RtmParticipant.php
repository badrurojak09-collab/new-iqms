<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class RtmParticipant extends Model
{
    use HasFactory;

    protected $table = 'rtm_participants';

    protected $fillable = ['rtm_meeting_id', 'user_id', 'role', 'attended'];

    protected function casts(): array
    {
        return ['attended' => 'boolean'];
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(RtmMeeting::class, 'rtm_meeting_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
