<?php

namespace LookstarKernel\Application\Tenant\Contacts\Models;

use LookstarKernel\Application\Tenant\Contacts\Traits\GetData;
use LookstarKernel\Support\Eloquent\TenantModel as Model;
use Composer\Support\Database\Models\Traits\UuidPrimaryKey;
use Illuminate\Support\Facades\Log;

class Contacts extends Model
{
    use UuidPrimaryKey;

    use GetData;

    protected $table = 'tenant_contacts';

    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'company',
        'industry',
        'job',
        'country',
        'province',
        'city',
        'district',
        'full_address',
        'source',
        'channel',
    ];

    protected $casts = [
        'source' => 'json',
    ];

    protected $appends = ['mask_data'];

    public function getMaskDataAttribute()
    {
        return $this->getMaskData($this->attributes);
    }

    public function getPlaintextDataAttribute()
    {
        return $this->getPlaintextData($this->attributes);
    }

    public static function sourceCreate($data)
    {
        if (isset($data['phone'])) {
            $contactsData = [];
            $contactsCoustomData = [];

            foreach ($data as $key => $value) {
                if (in_array($key, [
                    'full_name',
                    'email',
                    'phone',
                    'company',
                    'industry',
                    'job',
                    'country',
                    'province',
                    'city',
                    'district',
                    'full_address',
                    'source',
                    'channel',
                ])) {
                    $contactsData[$key] = $value;
                } elseif (stripos($key, 'custom') !== false) {
                    $contactsCoustomData[] = ['name' => $key, 'value' => $value];
                }
            }

            if (isset($contactsData['source']) && isset($contactsData['source']['openid'])) {
                ContactsBindOpenid::bind($contactsData['phone'], $contactsData['source']['openid']);
            }

            self::bindOpenid($contactsData);

            if ($contacts = self::where('phone', $contactsData['phone'])->first()) {
                foreach ($contactsData as $key => $value) {
                    if ($value && $key != 'source' && $key != 'channel') {
                        $contacts->$key = $value;
                    }
                }
                $contacts->save();
            } else {
                $contacts = self::create($contactsData);
            }
            if ($contactsCoustomData) {
                foreach ($contactsCoustomData as $key => $value) {
                    if ($custom = Custom::firstWhere(['contacts_id' => $contacts['id'], 'name' => $value['name']])) {
                        if ($value['value']) {
                            $custom->update(['value' => $value['value']]);
                        }
                    } else {
                        $value['contacts_id'] = $contacts['id'];
                        $custom = Custom::create($value);
                    }
                }
            }
        }
    }

    public function custom()
    {
        return $this->hasMany(Custom::class, 'contacts_id', 'id');
    }

    protected static function bindOpenid($contactsData)
    {
        if (isset($contactsData['source']) && isset($contactsData['source']['openid'])) {
            $time = time() * 1000;
            $trackId = mt_rand();
            $dateTime = date('Y-m-d H:i:s');
            $data = [
                'lookstar_tenant_id' => tenant()->getTenantKey(),
                'mobile' => $contactsData['phone'],
                'openid' => $contactsData['source']['openid'],
                'login_id' => $contactsData['source']['openid'],
                'distinct_id' => $contactsData['source']['openid'],
                'anonymous_id' => $contactsData['source']['openid'],
                'properties_lib' => 'server',
                'type' => 'track_id_bind',
                'event' => '$BindID',
                'time' => $time,
                '_track_id' => $trackId,
                '_flush_time' => $time,
                'create_time' => $dateTime,
                'receive_time' => $dateTime,
                'ds' => date('Ymd')
            ];
            if (isset($contactsData['source']['appid'])) {
                $data = [
                    'properties_mp_appid' => $contactsData['source']['appid']
                ];
            }
            Log::channel('analytics')->info('', $data);
        }
    }
}
