<?php

namespace App\Models;


class Sample2 extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'sample2';
    protected $primaryKey = 'sample2_id';
    public $relatedModels = ["phone"=>"many", "email"=>"many", "address"=>"one"];

    protected $validations = [
        "first_name" => "required|min:1|max:100",
        "last_name" => "required|min:1|max:100"
    ];

}
