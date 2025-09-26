<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('wilayats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('governorate_id')->constrained()->onDelete('cascade');
            $table->string('name_en');
            $table->string('name_ar');
            $table->string('code', 10);
            $table->text('description')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['governorate_id', 'name_en']);
            $table->unique(['governorate_id', 'name_ar']);
            $table->unique(['governorate_id', 'code']);
        });

        // Insert all Oman wilayats by governorate
        $wilayatsData = [
            // Muscat Governorate
            ['governorate_id' => 1, 'name_en' => 'Muscat', 'name_ar' => 'مسقط', 'code' => 'MSC01', 'latitude' => 23.5859, 'longitude' => 58.4059],
            ['governorate_id' => 1, 'name_en' => 'Muttrah', 'name_ar' => 'مطرح', 'code' => 'MSC02', 'latitude' => 23.6133, 'longitude' => 58.5933],
            ['governorate_id' => 1, 'name_en' => 'Bawshar', 'name_ar' => 'بوشر', 'code' => 'MSC03', 'latitude' => 23.5773, 'longitude' => 58.3997],
            ['governorate_id' => 1, 'name_en' => 'As Seeb', 'name_ar' => 'السيب', 'code' => 'MSC04', 'latitude' => 23.6701, 'longitude' => 58.1893],
            ['governorate_id' => 1, 'name_en' => 'Quriyat', 'name_ar' => 'قريات', 'code' => 'MSC05', 'latitude' => 23.2394, 'longitude' => 58.9644],
            ['governorate_id' => 1, 'name_en' => 'Amerat', 'name_ar' => 'العامرات', 'code' => 'MSC06', 'latitude' => 23.5864, 'longitude' => 58.5770],

            // Dhofar Governorate
            ['governorate_id' => 2, 'name_en' => 'Salalah', 'name_ar' => 'صلالة', 'code' => 'DHF01', 'latitude' => 17.0150, 'longitude' => 54.0924],
            ['governorate_id' => 2, 'name_en' => 'Taqah', 'name_ar' => 'طاقة', 'code' => 'DHF02', 'latitude' => 16.8833, 'longitude' => 53.9667],
            ['governorate_id' => 2, 'name_en' => 'Mirbat', 'name_ar' => 'مرباط', 'code' => 'DHF03', 'latitude' => 16.9789, 'longitude' => 54.7044],
            ['governorate_id' => 2, 'name_en' => 'Rakhyut', 'name_ar' => 'رخيوت', 'code' => 'DHF04', 'latitude' => 16.7833, 'longitude' => 53.2833],
            ['governorate_id' => 2, 'name_en' => 'Thumrait', 'name_ar' => 'ثمريت', 'code' => 'DHF05', 'latitude' => 17.6667, 'longitude' => 54.0333],
            ['governorate_id' => 2, 'name_en' => 'Dhalkut', 'name_ar' => 'ضلكوت', 'code' => 'DHF06', 'latitude' => 16.7500, 'longitude' => 53.2667],
            ['governorate_id' => 2, 'name_en' => 'Al Mazyunah', 'name_ar' => 'المزيونة', 'code' => 'DHF07', 'latitude' => 18.2667, 'longitude' => 56.3000],
            ['governorate_id' => 2, 'name_en' => 'Muqshin', 'name_ar' => 'مقشن', 'code' => 'DHF08', 'latitude' => 18.7167, 'longitude' => 55.9833],
            ['governorate_id' => 2, 'name_en' => 'Shaleem and Hallaniyat Islands', 'name_ar' => 'شليم وجزر الحلانيات', 'code' => 'DHF09', 'latitude' => 17.5000, 'longitude' => 56.0000],
            ['governorate_id' => 2, 'name_en' => 'Sadah', 'name_ar' => 'سدح', 'code' => 'DHF10', 'latitude' => 17.0333, 'longitude' => 54.9000],

            // Musandam Governorate
            ['governorate_id' => 3, 'name_en' => 'Khasab', 'name_ar' => 'خصب', 'code' => 'MSD01', 'latitude' => 26.1792, 'longitude' => 56.2456],
            ['governorate_id' => 3, 'name_en' => 'Bukha', 'name_ar' => 'بخاء', 'code' => 'MSD02', 'latitude' => 26.1167, 'longitude' => 56.1167],
            ['governorate_id' => 3, 'name_en' => 'Daba', 'name_ar' => 'دباء', 'code' => 'MSD03', 'latitude' => 25.6333, 'longitude' => 56.2667],
            ['governorate_id' => 3, 'name_en' => 'Madha', 'name_ar' => 'مدحاء', 'code' => 'MSD04', 'latitude' => 25.2833, 'longitude' => 56.2667],

            // Al Buraimi Governorate
            ['governorate_id' => 4, 'name_en' => 'Al Buraimi', 'name_ar' => 'البريمي', 'code' => 'ALB01', 'latitude' => 24.2382, 'longitude' => 55.7938],
            ['governorate_id' => 4, 'name_en' => 'Mahadah', 'name_ar' => 'محضة', 'code' => 'ALB02', 'latitude' => 24.4833, 'longitude' => 55.9167],
            ['governorate_id' => 4, 'name_en' => 'As Sunaynah', 'name_ar' => 'السنينة', 'code' => 'ALB03', 'latitude' => 24.3667, 'longitude' => 55.8000],

            // Ad Dakhiliyah Governorate
            ['governorate_id' => 5, 'name_en' => 'Nizwa', 'name_ar' => 'نزوى', 'code' => 'DAK01', 'latitude' => 22.9333, 'longitude' => 57.5333],
            ['governorate_id' => 5, 'name_en' => 'Bahla', 'name_ar' => 'بهلاء', 'code' => 'DAK02', 'latitude' => 22.9667, 'longitude' => 57.3000],
            ['governorate_id' => 5, 'name_en' => 'Manah', 'name_ar' => 'منح', 'code' => 'DAK03', 'latitude' => 23.2167, 'longitude' => 57.4167],
            ['governorate_id' => 5, 'name_en' => 'Al Hamra', 'name_ar' => 'الحمراء', 'code' => 'DAK04', 'latitude' => 23.1167, 'longitude' => 57.3000],
            ['governorate_id' => 5, 'name_en' => 'Adam', 'name_ar' => 'أدم', 'code' => 'DAK05', 'latitude' => 22.3833, 'longitude' => 57.5167],
            ['governorate_id' => 5, 'name_en' => 'Izki', 'name_ar' => 'إزكي', 'code' => 'DAK06', 'latitude' => 22.9333, 'longitude' => 57.7667],
            ['governorate_id' => 5, 'name_en' => 'Samail', 'name_ar' => 'سمائل', 'code' => 'DAK07', 'latitude' => 23.3167, 'longitude' => 57.9500],
            ['governorate_id' => 5, 'name_en' => 'Bidbid', 'name_ar' => 'بدبد', 'code' => 'DAK08', 'latitude' => 23.4167, 'longitude' => 58.1167],

            // North Al Batinah Governorate
            ['governorate_id' => 6, 'name_en' => 'Sohar', 'name_ar' => 'صحار', 'code' => 'NBA01', 'latitude' => 24.3667, 'longitude' => 56.7167],
            ['governorate_id' => 6, 'name_en' => 'Shinas', 'name_ar' => 'شناص', 'code' => 'NBA02', 'latitude' => 24.7167, 'longitude' => 56.4333],
            ['governorate_id' => 6, 'name_en' => 'Liwa', 'name_ar' => 'لوى', 'code' => 'NBA03', 'latitude' => 24.4667, 'longitude' => 56.5667],
            ['governorate_id' => 6, 'name_en' => 'Saham', 'name_ar' => 'صحم', 'code' => 'NBA04', 'latitude' => 24.1667, 'longitude' => 56.8833],
            ['governorate_id' => 6, 'name_en' => 'Al Khaburah', 'name_ar' => 'الخابورة', 'code' => 'NBA05', 'latitude' => 23.9667, 'longitude' => 57.0833],
            ['governorate_id' => 6, 'name_en' => 'As Suwaiq', 'name_ar' => 'السويق', 'code' => 'NBA06', 'latitude' => 23.8500, 'longitude' => 57.4333],

            // South Al Batinah Governorate
            ['governorate_id' => 7, 'name_en' => 'Rustaq', 'name_ar' => 'الرستاق', 'code' => 'SBA01', 'latitude' => 23.3833, 'longitude' => 57.4167],
            ['governorate_id' => 7, 'name_en' => 'Nakhal', 'name_ar' => 'نخل', 'code' => 'SBA02', 'latitude' => 23.3667, 'longitude' => 57.8167],
            ['governorate_id' => 7, 'name_en' => 'Wadi Al Maawil', 'name_ar' => 'وادي المعاول', 'code' => 'SBA03', 'latitude' => 23.2000, 'longitude' => 57.8000],
            ['governorate_id' => 7, 'name_en' => 'Awabi', 'name_ar' => 'العوابي', 'code' => 'SBA04', 'latitude' => 23.4833, 'longitude' => 57.5167],
            ['governorate_id' => 7, 'name_en' => 'Al Musanaah', 'name_ar' => 'المصنعة', 'code' => 'SBA05', 'latitude' => 23.6667, 'longitude' => 57.5167],
            ['governorate_id' => 7, 'name_en' => 'Barka', 'name_ar' => 'بركاء', 'code' => 'SBA06', 'latitude' => 23.6833, 'longitude' => 57.8833],

            // North Al Sharqiyah Governorate
            ['governorate_id' => 8, 'name_en' => 'Ibra', 'name_ar' => 'إبراء', 'code' => 'NSH01', 'latitude' => 22.6833, 'longitude' => 58.5333],
            ['governorate_id' => 8, 'name_en' => 'Al Mudaybi', 'name_ar' => 'المضيبي', 'code' => 'NSH02', 'latitude' => 22.5167, 'longitude' => 58.7667],
            ['governorate_id' => 8, 'name_en' => 'Al Qabil', 'name_ar' => 'القابل', 'code' => 'NSH03', 'latitude' => 22.2167, 'longitude' => 59.2167],
            ['governorate_id' => 8, 'name_en' => 'Wadi Bani Khalid', 'name_ar' => 'وادي بني خالد', 'code' => 'NSH04', 'latitude' => 22.6167, 'longitude' => 59.0667],
            ['governorate_id' => 8, 'name_en' => 'Dima Wa Tayyin', 'name_ar' => 'دماء والطائيين', 'code' => 'NSH05', 'latitude' => 23.3500, 'longitude' => 58.2000],
            ['governorate_id' => 8, 'name_en' => 'Bidiyah', 'name_ar' => 'بدية', 'code' => 'NSH06', 'latitude' => 22.2167, 'longitude' => 58.7833],

            // South Al Sharqiyah Governorate
            ['governorate_id' => 9, 'name_en' => 'Sur', 'name_ar' => 'صور', 'code' => 'SSH01', 'latitude' => 22.5667, 'longitude' => 59.5289],
            ['governorate_id' => 9, 'name_en' => 'Al Kamil Wa Al Wafi', 'name_ar' => 'الكامل والوافي', 'code' => 'SSH02', 'latitude' => 22.0167, 'longitude' => 59.2333],
            ['governorate_id' => 9, 'name_en' => 'Jaalan Bani Bu Hassan', 'name_ar' => 'جعلان بني بو حسن', 'code' => 'SSH03', 'latitude' => 21.8500, 'longitude' => 59.1333],
            ['governorate_id' => 9, 'name_en' => 'Jaalan Bani Bu Ali', 'name_ar' => 'جعلان بني بو علي', 'code' => 'SSH04', 'latitude' => 21.6833, 'longitude' => 59.0333],
            ['governorate_id' => 9, 'name_en' => 'Masirah', 'name_ar' => 'مصيرة', 'code' => 'SSH05', 'latitude' => 20.6667, 'longitude' => 58.8833],

            // Ad Dhahirah Governorate
            ['governorate_id' => 10, 'name_en' => 'Ibri', 'name_ar' => 'عبري', 'code' => 'DHA01', 'latitude' => 23.2256, 'longitude' => 56.5158],
            ['governorate_id' => 10, 'name_en' => 'Yanqul', 'name_ar' => 'ينقل', 'code' => 'DHA02', 'latitude' => 23.5833, 'longitude' => 56.5500],
            ['governorate_id' => 10, 'name_en' => 'Dhank', 'name_ar' => 'ضنك', 'code' => 'DHA03', 'latitude' => 23.6667, 'longitude' => 56.7833],

            // Al Wusta Governorate
            ['governorate_id' => 11, 'name_en' => 'Haima', 'name_ar' => 'هيماء', 'code' => 'WST01', 'latitude' => 19.6333, 'longitude' => 56.3167],
            ['governorate_id' => 11, 'name_en' => 'Mahout', 'name_ar' => 'محوت', 'code' => 'WST02', 'latitude' => 19.4833, 'longitude' => 56.0000],
            ['governorate_id' => 11, 'name_en' => 'Al Duqm', 'name_ar' => 'الدقم', 'code' => 'WST03', 'latitude' => 19.6500, 'longitude' => 57.8833],
            ['governorate_id' => 11, 'name_en' => 'Jazer Al Hallaniyyah', 'name_ar' => 'جازر الحلانية', 'code' => 'WST04', 'latitude' => 17.5000, 'longitude' => 56.0000],
        ];

        foreach ($wilayatsData as $wilayat) {
            DB::table('wilayats')->insert(array_merge($wilayat, [
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wilayats');
    }
};
