<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $faqs = [
            [
                'question' => 'Kapan pendaftaran santri baru dibuka?',
                'answer' => 'Pendaftaran santri baru dibuka setiap tahun pada bulan Januari hingga Juni untuk tahun ajaran baru yang dimulai pada bulan Juli.',
                'order' => 1,
            ],
            [
                'question' => 'Apa saja syarat pendaftaran?',
                'answer' => 'Syarat pendaftaran meliputi: (1) Usia minimal 12 tahun, (2) Foto copy ijazah terakhir, (3) Foto copy KK dan KTP orang tua, (4) Pas foto 3x4 sebanyak 4 lembar, (5) Surat keterangan sehat dari dokter.',
                'order' => 2,
            ],
            [
                'question' => 'Berapa biaya pendaftaran dan SPP?',
                'answer' => 'Biaya pendaftaran sebesar Rp 500.000 (sekali bayar). SPP bulanan sebesar Rp 250.000 sudah termasuk biaya asrama, makan 3x sehari, dan kegiatan belajar.',
                'order' => 3,
            ],
            [
                'question' => 'Apakah tersedia beasiswa?',
                'answer' => 'Ya, kami menyediakan beasiswa penuh dan parsial bagi santri berprestasi dan kurang mampu. Beasiswa dapat diajukan saat pendaftaran dengan melampirkan dokumen pendukung.',
                'order' => 4,
            ],
            [
                'question' => 'Bagaimana sistem pembelajaran di pondok?',
                'answer' => 'Kami menggabungkan sistem pendidikan formal (MI/MTs/MA) dengan pendidikan pesantren (tahfidz, kitab kuning). Santri akan mendapat ijazah formal dan sertifikat pondok.',
                'order' => 5,
            ],
            [
                'question' => 'Apakah santri diperbolehkan membawa HP?',
                'answer' => 'HP hanya diperbolehkan untuk komunikasi dengan wali santri pada waktu yang telah ditentukan (weekend). Pada hari efektif, HP disimpan di sekretariat pondok.',
                'order' => 6,
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::create([
                ...$faq,
                'is_active' => true,
            ]);
        }
    }
}
