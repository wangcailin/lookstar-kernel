<?php

namespace LookstarKernel\Application\Tenant\Contacts\Models;

use LookstarKernel\Application\Central\Contacts\Models\FieldRule;
use Composer\Exceptions\ApiErrorCode;
use Composer\Exceptions\ApiException;
use Illuminate\Database\Eloquent\Model;

class Field extends Model
{
    protected $table = 'tenant_contacts_field';

    protected $fillable = [
        'tenant_id',
        'rule_name',
        'type',
        'form_type',
        'name',
        'label',
        'placeholder',
        'options',
    ];

    protected $casts = [
        'options' => 'json',
    ];

    public static $EnumFormType = ['text', 'radio', 'checkbox', 'select', 'date', 'address'];
    public static $SystemFieldsRule = ['phone' => ['regexp_ch_phone', 'regexp_ch_code_phone'], 'email' => ['regexp_company_email', 'regexp_code_email', 'regexp_email']];

    protected static function booting()
    {
        static::addGlobalScope(new TenantScope());
        static::creating(function ($model) {
            if ($model->type != 'system') {
                $model->type = 'custom';
                $model->name = "{$model->type}_" . uniqid();
                $model->tenant_id = tenant('id');
            }
        });

        static::updating(function ($model) {
            if (in_array($model->name, ['phone', 'email'])) {
                if (!in_array($model->rule_name, self::$SystemFieldsRule[$model->name])) {
                    throw new ApiException('参数错误', ApiErrorCode::VALIDATION_ERROR);
                }
            }
        });
    }

    public function rule()
    {
        return $this->hasOne(FieldRule::class, 'name', 'rule_name')->select('name', 'label');
    }
}
