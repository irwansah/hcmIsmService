<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "employee".
 *
 * @property string $person_id
 * @property string $nik
 * @property string $nama
 * @property string $title
 * @property string $tanggal_masuk
 * @property string $employee_category
 * @property string $organization
 * @property string $job
 * @property string $band
 * @property string $location
 * @property string $kota
 * @property string $no_hp
 * @property string $email
 * @property string $gender
 * @property string $status_pernikahan
 * @property string $agama
 * @property string $tgl_lahir
 * @property string $kota_lahir
 * @property string $start_date_assignment
 * @property string $admins
 * @property string $nik_atasan
 * @property string $nama_atasan
 * @property string $medical_admin
 * @property string $section
 * @property string $department
 * @property string $division
 * @property string $bgroup
 * @property string $egroup
 * @property string $directorate
 * @property string $area
 * @property string $tgl_masuk
 * @property string $status
 * @property string $status_employee
 * @property string $start_date_status
 * @property string $end_date_status
 * @property string $bp
 * @property string $bi
 * @property string $edu_lvl
 * @property string $edu_faculty
 * @property string $edu_major
 * @property string $edu_institution
 * @property string $posisi
 * @property string $last_update_date
 * @property int $salary
 * @property int $tunjangan
 * @property int $tunjangan_jabatan
 * @property double $tunjangan_rekomposisi
 * @property string $structural
 * @property string $functional
 * @property string $no_ktp
 * @property string $suku
 * @property string $golongan_darah
 * @property string $no_npwp
 * @property string $alamat
 * @property string $nama_ibu
 * @property string $dpe
 * @property string $kode_kota
 * @property int $position_id
 * @property string $homebase
 * @property string $job_category
 * @property int $job_id
 */
class Employee extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'employee';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['person_id'], 'required'],
            [['tanggal_masuk', 'tgl_lahir', 'start_date_assignment', 'tgl_masuk', 'start_date_status', 'end_date_status', 'last_update_date', 'dpe'], 'safe'],
            [['kota_lahir', 'edu_faculty', 'edu_major', 'alamat'], 'string'],
            [['salary', 'tunjangan', 'tunjangan_jabatan', 'position_id', 'job_id'], 'integer'],
            [['tunjangan_rekomposisi'], 'number'],
            [['person_id', 'nik', 'no_hp', 'nik_atasan', 'status_employee'], 'string', 'max' => 25],
            [['nama', 'job', 'location', 'email', 'nama_atasan', 'edu_institution'], 'string', 'max' => 128],
            [['title', 'organization', 'section', 'department', 'division', 'bgroup', 'egroup', 'directorate'], 'string', 'max' => 255],
            [['employee_category', 'agama'], 'string', 'max' => 15],
            [['band'], 'string', 'max' => 1],
            [['kota', 'admins', 'medical_admin', 'area', 'status', 'no_ktp', 'suku', 'golongan_darah', 'no_npwp', 'nama_ibu', 'homebase'], 'string', 'max' => 45],
            [['gender', 'status_pernikahan'], 'string', 'max' => 10],
            [['bp', 'bi', 'structural', 'functional', 'kode_kota'], 'string', 'max' => 5],
            [['edu_lvl'], 'string', 'max' => 32],
            [['posisi'], 'string', 'max' => 80],
            [['job_category'], 'string', 'max' => 200],
            [['person_id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'person_id' => 'Person ID',
            'nik' => 'Nik',
            'nama' => 'Nama',
            'title' => 'Title',
            'tanggal_masuk' => 'Tanggal Masuk',
            'employee_category' => 'Employee Category',
            'organization' => 'Organization',
            'job' => 'Job',
            'band' => 'Band',
            'location' => 'Location',
            'kota' => 'Kota',
            'no_hp' => 'No Hp',
            'email' => 'Email',
            'gender' => 'Gender',
            'status_pernikahan' => 'Status Pernikahan',
            'agama' => 'Agama',
            'tgl_lahir' => 'Tgl Lahir',
            'kota_lahir' => 'Kota Lahir',
            'start_date_assignment' => 'Start Date Assignment',
            'admins' => 'Admins',
            'nik_atasan' => 'Nik Atasan',
            'nama_atasan' => 'Nama Atasan',
            'medical_admin' => 'Medical Admin',
            'section' => 'Section',
            'department' => 'Department',
            'division' => 'Division',
            'bgroup' => 'Bgroup',
            'egroup' => 'Egroup',
            'directorate' => 'Directorate',
            'area' => 'Area',
            'tgl_masuk' => 'Tgl Masuk',
            'status' => 'Status',
            'status_employee' => 'Status Employee',
            'start_date_status' => 'Start Date Status',
            'end_date_status' => 'End Date Status',
            'bp' => 'Bp',
            'bi' => 'Bi',
            'edu_lvl' => 'Edu Lvl',
            'edu_faculty' => 'Edu Faculty',
            'edu_major' => 'Edu Major',
            'edu_institution' => 'Edu Institution',
            'posisi' => 'Posisi',
            'last_update_date' => 'Last Update Date',
            'salary' => 'Salary',
            'tunjangan' => 'Tunjangan',
            'tunjangan_jabatan' => 'Tunjangan Jabatan',
            'tunjangan_rekomposisi' => 'Tunjangan Rekomposisi',
            'structural' => 'Structural',
            'functional' => 'Functional',
            'no_ktp' => 'No Ktp',
            'suku' => 'Suku',
            'golongan_darah' => 'Golongan Darah',
            'no_npwp' => 'No Npwp',
            'alamat' => 'Alamat',
            'nama_ibu' => 'Nama Ibu',
            'dpe' => 'Dpe',
            'kode_kota' => 'Kode Kota',
            'position_id' => 'Position ID',
            'homebase' => 'Homebase',
            'job_category' => 'Job Category',
            'job_id' => 'Job ID',
        ];
    }
}
