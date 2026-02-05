<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Email extends Model {
    protected $fillable = ['user_id','email_template_id'];
    public function user() { return $this->belongsTo(User::class); }
    public function template() { return $this->belongsTo(EmailTemplate::class, 'email_template_id'); }
}
