<?php

namespace Incasso\Models;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model {
    public $timestamps = false;
    public $primaryKey = 'id';
    protected $validator_class = 'Incasso\\Validators\\Validator';
    protected $validator;

    public function validates() {
        if (is_null($this->validator_class)) {
            return true;
        }
        $validator = new $this->validator_class();
        return $validator->validate($this);
    }

    public static function tablename() {
        $model = get_called_class();
        return (new $model())->table;
    }

    public function cleanup() {
        Capsule::schema()->dropIfExists($this->table);
    }
}
