<?php

namespace cms\models;

use Yii;

/**
 * This is the model class for table "employee".
 *
 * @property string $person_id
 * @property string|null $nik
 * @property string|null $nama
 * @property string|null $title
 * @property string|null $tanggal_masuk
 * @property string|null $employee_category
 * @property string|null $organization
 * @property string|null $job
 * @property string|null $band
 * @property string|null $location
 * @property string|null $kota
 * @property string|null $no_hp
 * @property string|null $email
 * @property string|null $gender
 * @property string|null $status_pernikahan
 * @property string|null $agama
 * @property string|null $tgl_lahir
 * @property string|null $kota_lahir
 * @property string|null $start_date_assignment
 * @property string|null $admins
 * @property string|null $nik_atasan
 * @property string|null $nama_atasan
 * @property string|null $medical_admin
 * @property string|null $section
 * @property string|null $department
 * @property string|null $division
 * @property string|null $bgroup
 * @property string|null $egroup
 * @property string|null $directorate
 * @property string|null $area
 * @property string|null $tgl_masuk
 * @property string|null $status
 * @property string|null $status_employee
 * @property string|null $start_date_status
 * @property string|null $end_date_status
 * @property string|null $bp
 * @property string|null $bi
 * @property int|null $score
 * @property string|null $edu_lvl
 * @property string|null $edu_faculty
 * @property string|null $edu_major
 * @property string|null $edu_institution
 * @property string|null $posisi
 * @property string|null $last_update_date
 * @property int|null $salary
 * @property int|null $tunjangan
 * @property int|null $tunjangan_jabatan
 * @property float|null $tunjangan_rekomposisi
 * @property string|null $structural
 * @property string|null $functional
 * @property string|null $no_ktp
 * @property string|null $suku
 * @property string|null $golongan_darah
 * @property string|null $no_npwp
 * @property string|null $alamat
 * @property string|null $nama_ibu
 * @property string|null $dpe
 * @property string|null $kode_kota
 * @property int|null $position_id
 * @property string|null $homebase
 * @property string|null $job_category
 * @property int|null $job_id
 * @property string|null $timezone
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
            [['score', 'salary', 'tunjangan', 'tunjangan_jabatan', 'position_id', 'job_id'], 'integer'],
            [['tunjangan_rekomposisi'], 'number'],
            [['person_id', 'nik', 'no_hp', 'nik_atasan', 'status_employee'], 'string', 'max' => 25],
            [['nama', 'job', 'location', 'email', 'nama_atasan', 'edu_institution'], 'string', 'max' => 128],
            [['title', 'organization', 'section', 'department', 'division', 'bgroup', 'egroup', 'directorate'], 'string', 'max' => 255],
            [['employee_category', 'agama'], 'string', 'max' => 15],
            [['band'], 'string', 'max' => 1],
            [['kota', 'admins', 'medical_admin', 'area', 'status', 'no_ktp', 'suku', 'golongan_darah', 'no_npwp', 'nama_ibu', 'homebase', 'timezone'], 'string', 'max' => 45],
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
            'score' => 'Score',
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
            'timezone' => 'Timezone',
        ];
    }
}
