<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Candidate;

class CandidateSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    Candidate::create([
      'candidate_pair' => 1,
      'leader_name' => 'Zakha',
      'vice_leader_name' => 'Natasya',
      'vision' => 'Menciptakan lingkungan Himpunan Mahasiswa Informatika yang memotivasi, menginspirasi, dan memberdayakan mahasiswa Informatika untuk berkembang secara profesional.',
      'mission' => 'Menumbuhkan semangat juang Himpunan Mahasiswa Informatika kepada seluruh Mahasiswa Informatika demi tercapainya tujuan akademis dan sosial.
      Mendukung pengembangan potensi dengan menyediakan platform dan sumber daya yang memfasilitasi eksplorasi, pengembangan, dan pemanfaatan potensi yang dimiliki.
      Meningkatkan sinergi di antara pengurus dan anggota himpunan mahasiswa informatika guna menciptakan lingkungan yang saling mendukung baik dalam aspek akademik maupun non-akademik.
      ',
      'created_at' => now(),
      'updated_at' => now()
    ]);
    Candidate::create([
      'candidate_pair' => 2,
      'leader_name' => 'Gertrudis',
      'vice_leader_name' => 'aydin',
      'vision' => 'Menjadikan Himpunan Mahasiswa Informatika sebagai wadah perubahan untuk mengembangkan potensi, kreativitas, sifat proaktif, serta membangun lingkungan yang harmonis bagi semua anggota dan pengurus Himpunan Mahasiswa Informatika.',
      'mission' => 'Memberikan peluang dan program pengembangan untuk membantu semua anggota dan pengurus Himpunan Mahasiswa Informatika, mencapai potensi penuh mereka secara pribadi dan profesional.
      Mendorong dan menghargai setiap kreativitas yang digali oleh para anggota dan pengurus Himpunan Mahasiswa Informatika.
      Mendukung setiap anggota dan pengurus Himpunan Mahasiswa Informatika untuk mengambil inisiatif, dan bertindak proaktif.
      Membangun budaya komunikasi yang terbuka dan transparan antaranggota dan pengurus Himpunan Mahasiswa Informatika.
      ',
      'created_at' => now(),
      'updated_at' => now()
    ]);
  }
}
