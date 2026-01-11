<!DOCTYPE html>
<html>
<head>
    <title>Pendaftaran Santri Baru</title>
    <style>
        body { font-family: sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f2f2f2; }
        .section-title { font-weight: bold; margin-top: 20px; margin-bottom: 10px; border-bottom: 1px solid #000; padding-bottom: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Data Pendaftaran Santri Baru</h2>
        <h3>{{ $registration->boardingSchool->name }}</h3>
    </div>

    <p><strong>Nomor Pendaftaran:</strong> {{ $registration->registration_number }}</p>
    <p><strong>Tanggal Daftar:</strong> {{ $registration->created_at->format('d F Y') }}</p>
    <p><strong>Status:</strong> {{ ucfirst($registration->status) }}</p>

    <div class="section-title">A. Data Diri Santri</div>
    <table class="table">
        <tr><th width="30%">Nama Lengkap</th><td>{{ $registration->name }}</td></tr>
        <tr><th>NISN</th><td>-</td></tr>
        <tr><th>Tempat, Tanggal Lahir</th><td>{{ $registration->place_of_birth }}, {{ $registration->date_of_birth?->format('d-m-Y') }}</td></tr>
        <tr><th>Jenis Kelamin</th><td>{{ $registration->gender?->label() }}</td></tr>
        <tr><th>Alamat</th><td>{{ $registration->address }}</td></tr>
        <tr><th>Desa/Kelurahan</th><td>{{ $registration->village }}</td></tr>
        <tr><th>Kecamatan</th><td>{{ $registration->district }}</td></tr>
        <tr><th>Kabupaten/Kota</th><td>{{ $registration->regency }}</td></tr>
        <tr><th>Provinsi</th><td>{{ $registration->province }}</td></tr>
    </table>

    <div class="section-title">B. Data Orang Tua</div>
    <table class="table">
        <tr><th width="30%">Nama Ayah</th><td>{{ $registration->father_name }}</td></tr>
        <tr><th>Pekerjaan Ayah</th><td>{{ $registration->father_job }}</td></tr>
        <tr><th>No HP Ayah</th><td>{{ $registration->father_phone }}</td></tr>
        <tr><th>Nama Ibu</th><td>{{ $registration->mother_name }}</td></tr>
        <tr><th>Pekerjaan Ibu</th><td>{{ $registration->mother_job }}</td></tr>
        <tr><th>No HP Ibu</th><td>{{ $registration->mother_phone }}</td></tr>
    </table>

    <div class="section-title">C. Data Akademik</div>
    <table class="table">
        <tr><th width="30%">Sekolah Formal</th><td>{{ $registration->school?->name ?? '-' }}</td></tr>
        <tr><th>Tingkat/Kelas</th><td>{{ $registration->schoolLevel?->name ?? '-' }}</td></tr>
    </table>

</body>
</html>
