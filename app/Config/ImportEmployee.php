<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class ImportEmployee extends BaseConfig
{
    public array $fields = [

        // direct fields (no relation)
        'nik' => [
            'type' => 'direct',
        ],

        'nama' => [
            'type' => 'direct',
            'db_field' => 'name'
        ],

        'user' => [
            'type' => 'direct',
            'db_field' => 'work_user'
        ],

        'tanggal pkwt' => [
            'type'     => 'date',
            'db_field' => 'pkwt_date',
            'format'   => 'd/m/Y', // the format used IN the Excel file
        ],

        'tanggal resign/phk' => [
            'type'     => 'date',
            'db_field' => 'cutoff_date',
            'format'   => 'd/m/Y',
        ],

        'birth date' => [
            'type'     => 'date',
            'db_field' => 'date_of_birth',
            'format'   => 'd/m/Y',
        ],

        'place of birth' => [
            'type' => 'direct',
            'db_field' => 'place_of_birth'
        ],

        'national id' => [
            'type' => 'direct',
            'db_field' => 'national_id'
        ],

        'phone number' => [
            'type' => 'direct',
            'db_field' => 'phone_number'
        ],

        'address' => [
            'type' => 'direct',
        ],

        // master relation fields
        'department' => [
            'type' => 'master',
            'model' => 'dept',
            'db_field' => 'department_id'
        ],

        'division' => [
            'type' => 'master',
            'model' => 'div',
            'db_field' => 'division_id'
        ],

        'job position' => [
            'type' => 'master',
            'model' => 'grp',
            'db_field' => 'job_position_id',
            'partial_match' => true
        ],

        'gender' => [
            'type' => 'master',
            'model' => 'gend',
            'db_field' => 'gender_id'
        ],

        'religion' => [
            'type' => 'master',
            'model' => 'rlg',
            'db_field' => 'religion_id'
        ],

        'site name' => [
            'type' => 'master',
            'model' => 'site',
            'db_field' => 'site_id'
        ],

        'employee status' => [
            'type' => 'master',
            'model' => 'empss',
            'db_field' => 'employee_status_id'
        ],

        'employment status' => [
            'type' => 'master',
            'model' => 'empns',
            'db_field' => 'employment_status_id'
        ],

        'last education' => [
            'type' => 'master',
            'model' => 'edu',
            'db_field' => 'last_education_id'
        ]
    ];
}