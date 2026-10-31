<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\MediaLibrary;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductStoryPhase;
use App\Models\SiteSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RashfaContentSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $categories = collect([
                ['name_ar'=>'حمضية','name_en'=>'Citrus','slug'=>'citrus','description_ar'=>'نكهات مشرقة بنفحات حمضيات دقيقة.','description_en'=>'Bright blends with vivid citrus notes.','sort_order'=>1],
                ['name_ar'=>'حلوة','name_en'=>'Sweet','slug'=>'sweet','description_ar'=>'مذاقات ناعمة بطبيعتها الحلوة.','description_en'=>'Soft, naturally sweet selections.','sort_order'=>2],
                ['name_ar'=>'استوائية','name_en'=>'Tropical','slug'=>'tropical','description_ar'=>'رحلة فاكهية استوائية غنية.','description_en'=>'A lush journey through tropical fruit.','sort_order'=>3],
            ])->mapWithKeys(fn ($row) => [$row['slug'] => Category::updateOrCreate(['slug'=>$row['slug']], $row + ['is_active'=>true])]);

            $ingredients = collect([
                ['name_ar'=>'برتقال','name_en'=>'Orange','slug'=>'orange','description_ar'=>'برتقال طبيعي ناضج في لحظة توازنه.','description_en'=>'Naturally ripe orange at its brightest.'],
                ['name_ar'=>'رمان','name_en'=>'Pomegranate','slug'=>'pomegranate','description_ar'=>'حبّات رمان كاملة النكهة.','description_en'=>'Full-flavored pomegranate arils.'],
                ['name_ar'=>'جوافة','name_en'=>'Guava','slug'=>'guava','description_ar'=>'جوافة عطرية بقوام غني.','description_en'=>'Fragrant guava with a rich body.'],
                ['name_ar'=>'ليمون أخضر','name_en'=>'Green Lime','slug'=>'green-lime','description_ar'=>'ليمون أخضر يشدّ النهاية بانتعاش نظيف.','description_en'=>'Green lime for a crisp finish.'],
                ['name_ar'=>'مانجو','name_en'=>'Mango','slug'=>'mango','description_ar'=>'مانجو مختارة بقوام مخملي.','description_en'=>'Selected mango with velvety body.'],
                ['name_ar'=>'نعناع','name_en'=>'Mint','slug'=>'mint','description_ar'=>'نعناع بارد يترك أثراً عطرياً.','description_en'=>'Cool mint with an aromatic finish.'],
            ])->mapWithKeys(fn ($row) => [$row['slug'] => Ingredient::updateOrCreate(['slug'=>$row['slug']], $row + ['is_active'=>true])]);

            $mediaFiles = [
                'orange' => 'photo-1600271886742-f049cd451bba.jpeg', 'pomegranate' => 'photo-1614707585284-9cb9fc018387.jpeg',
                'guava' => 'photo-1621506289937-a8e4df240d0b.jpeg', 'lime' => 'kiwi.webp',
                'mango' => 'photo-1603569283847-aa295f0d016a.jpeg', 'berry' => 'photo-1534353473418-4cfa6c56fd38.jpeg',
                'bottle' => 'bottle-poster.webp', 'orchard' => 'photo-1497534446932-c925b458314e.jpeg', 'press' => 'photo-1546173159-315724a31696.jpeg', 'sip' => 'photo-1607690506833-498e04ab3ffa.jpeg',
            ];
            $media = collect($mediaFiles)->mapWithKeys(function ($file, $key) {
                $path = public_path('media/rashfa/'.$file); $dimensions = @getimagesize($path) ?: [null, null];
                return [$key => MediaLibrary::updateOrCreate(['storage_path'=>'/media/rashfa/'.$file], ['file_name'=>$file, 'mime_type'=>is_file($path) ? (mime_content_type($path) ?: 'image/jpeg') : 'image/jpeg', 'file_size_bytes'=>is_file($path) ? filesize($path) : 0, 'width_px'=>$dimensions[0], 'height_px'=>$dimensions[1], 'checksum_sha256'=>is_file($path) ? hash_file('sha256',$path) : null])];
            });

            $products = [
                ['slug'=>'cinematic-orange','category'=>'citrus','name_ar'=>'برتقال سينمائي','name_en'=>'Cinematic Orange','short_desc_ar'=>'برتقال شمسي بطبقات عطرية تنساب كالمشهد الأخير.','short_desc_en'=>'Sunlit orange with aromatic layers that unfold like a final scene.','long_desc_ar'=>'نلتقط للبرتقال لحظة واحدة: حين يصبح الضوء نكهة. مزيج متوازن بين اللبّ والقشرة العطرية.','long_desc_en'=>'We capture one moment of orange: when light becomes flavor.','sip_story_ar'=>'حكاية شمسٍ تُحفظ في زجاجة، من البستان حتى آخر رشفة.','sip_story_en'=>'A little bottled sunshine, from orchard to final sip.','accent'=>'#C6F135','nutrition'=>['calories_kcal'=>46,'sugar_g'=>9.2,'vitamin_c_mg'=>52,'serving_ml'=>100],'tags'=>['حمضيات','منعش','صباحي'],'ingredients'=>[['orange',92],['green-lime',8]],'media'=>'orange','sort'=>1,'featured'=>true],
                ['slug'=>'midnight-pomegranate','category'=>'sweet','name_ar'=>'رمان ليلي','name_en'=>'Midnight Pomegranate','short_desc_ar'=>'رمان عميق يوقظ حلاوته في نهاية المساء.','short_desc_en'=>'Deep pomegranate revealing sweetness after dusk.','long_desc_ar'=>'حبّات رمان داكنة وعصير متوازن بلمسة مخملية فاخرة.','long_desc_en'=>'Dark pomegranate meets a balanced velvet finish.','sip_story_ar'=>'ليلة هادئة بلون الياقوت، صيغت لتبقى في الذاكرة.','sip_story_en'=>'A quiet ruby-colored night made to linger.','accent'=>'#D4A24C','nutrition'=>['calories_kcal'=>54,'sugar_g'=>11.4,'vitamin_c_mg'=>8,'serving_ml'=>100],'tags'=>['رمان','فاخر','مسائي'],'ingredients'=>[['pomegranate',90],['orange',10]],'media'=>'pomegranate','sort'=>2,'featured'=>true],
                ['slug'=>'dawn-guava','category'=>'tropical','name_ar'=>'جوافة الفجر','name_en'=>'Dawn Guava','short_desc_ar'=>'جوافة ناعمة تستيقظ على نسيم الفجر.','short_desc_en'=>'Silky guava awakened by the first breeze of dawn.','long_desc_ar'=>'لبّ جوافة ناضج بعناية يمنح قواماً غنياً وانتعاشاً متوازناً.','long_desc_en'=>'Thoughtfully ripened guava pulp delivers a rich body.','sip_story_ar'=>'قطرات الندى الأولى تحمل عبير الجوافة من البستان.','sip_story_en'=>'Dew carries guava fragrance from the orchard.','accent'=>'#C6F135','nutrition'=>['calories_kcal'=>49,'sugar_g'=>10.1,'vitamin_c_mg'=>90,'serving_ml'=>100],'tags'=>['جوافة','ناعم','استوائي'],'ingredients'=>[['guava',88],['mango',12]],'media'=>'guava','sort'=>3,'featured'=>true],
                ['slug'=>'royal-green-lime','category'=>'citrus','name_ar'=>'ليمون أخضر ملكي','name_en'=>'Royal Green Lime','short_desc_ar'=>'ليمون أخضر جريء يترك ختاماً نبيلاً ونظيفاً.','short_desc_en'=>'Bold green lime with a crisp, noble finish.','long_desc_ar'=>'عصير ليمون أخضر منعش ترفعه نفحات نباتية ولمسة حلاوة رقيقة.','long_desc_en'=>'Refreshing green lime lifted by botanicals.','sip_story_ar'=>'أناقة خضراء تبدأ بقطفة طازجة وتنتهي بانتعاش صافٍ.','sip_story_en'=>'Green elegance from a fresh pick to a clean finish.','accent'=>'#C6F135','nutrition'=>['calories_kcal'=>31,'sugar_g'=>6.8,'vitamin_c_mg'=>29,'serving_ml'=>100],'tags'=>['ليمون','حامض','نباتي'],'ingredients'=>[['green-lime',84],['orange',16]],'media'=>'lime','sort'=>4,'featured'=>false],
                ['slug'=>'sunrise-mango','category'=>'tropical','name_ar'=>'مانجو الشروق','name_en'=>'Sunrise Mango','short_desc_ar'=>'مانجو مخملية بلون الشمس عند أول النهار.','short_desc_en'=>'Velvety mango in the color of first light.','long_desc_ar'=>'مانجو ناضجة بقوام حريري وتوازن بين العسل والفاكهة الطازجة.','long_desc_en'=>'Ripe mango with a silky, honey-fresh balance.','sip_story_ar'=>'حين يلامس الضوء الثمرة، تبدأ حكاية الحلاوة.','sip_story_en'=>'When light touches fruit, sweetness starts.','accent'=>'#D4A24C','nutrition'=>['calories_kcal'=>60,'sugar_g'=>13.7,'vitamin_c_mg'=>36,'serving_ml'=>100],'tags'=>['مانجو','مخملي','فاخر'],'ingredients'=>[['mango',91],['orange',9]],'media'=>'mango','sort'=>5,'featured'=>false],
                ['slug'=>'violet-berry','category'=>'sweet','name_ar'=>'توت بنفسجي','name_en'=>'Violet Berry','short_desc_ar'=>'مزيج توت أنيق يجمع اللمعان والحموضة الرقيقة.','short_desc_en'=>'An elegant berry blend with gentle tartness.','long_desc_ar'=>'توت مختار ينسج طبقات فاكهية غنية ولوناً بنفسجياً طبيعياً.','long_desc_en'=>'Selected berries weave rich fruit layers.','sip_story_ar'=>'كل ثمرة حكاية صغيرة من موسمٍ قصيرٍ ومذاقٍ طويل.','sip_story_en'=>'Each berry holds a brief season and a long taste.','accent'=>'#D4A24C','nutrition'=>['calories_kcal'=>43,'sugar_g'=>8.6,'vitamin_c_mg'=>22,'serving_ml'=>100],'tags'=>['توت','موسمي','متوازن'],'ingredients'=>[['pomegranate',45],['orange',55]],'media'=>'berry','sort'=>6,'featured'=>false],
            ];
            foreach ($products as $row) {
                $product = Product::updateOrCreate(['slug'=>$row['slug']], ['category_id'=>$categories[$row['category']]->id,'name_ar'=>$row['name_ar'],'name_en'=>$row['name_en'],'short_desc_ar'=>$row['short_desc_ar'],'short_desc_en'=>$row['short_desc_en'],'long_desc_ar'=>$row['long_desc_ar'],'long_desc_en'=>$row['long_desc_en'],'sip_story_ar'=>$row['sip_story_ar'],'sip_story_en'=>$row['sip_story_en'],'base_color'=>'#0E3B2E','accent_color'=>$row['accent'],'nutrition_json'=>$row['nutrition'],'tags_json'=>$row['tags'],'sort_order'=>$row['sort'],'is_published'=>true,'is_featured'=>$row['featured'],'is_active'=>true]);
                $product->ingredients()->sync(collect($row['ingredients'])->mapWithKeys(fn ($i, $k)=>[$ingredients[$i[0]]->id=>['percentage'=>$i[1],'sort_order'=>$k+1]])->all());
                foreach ([[250,18,22,1],[750,42,49,2]] as [$size,$price,$compare,$order]) $product->variants()->updateOrCreate(['size_ml'=>$size], ['label_ar'=>'عبوة '.($size === 250 ? '٢٥٠' : '٧٥٠').' مل','label_en'=>"$size ml bottle",'price'=>$price,'compare_at_price'=>$compare,'sku'=>strtoupper(str_replace('-','_',$row['slug']))."_$size",'stock_quantity'=>0,'is_active'=>true,'sort_order'=>$order]);
                ProductImage::where('product_id',$product->id)->update(['is_primary'=>false]);
                ProductImage::updateOrCreate(['product_id'=>$product->id,'media_id'=>$media[$row['media']]->id], ['image_type'=>'hero','alt_ar'=>$row['name_ar'].' — لقطة رئيسية للنكهة','is_primary'=>true,'sort_order'=>1]);
            }
            $orange = Product::where('slug','cinematic-orange')->firstOrFail();
            foreach ([['harvest','نقطف في أوانها','Picked at peak','نختار البرتقال عند اكتمال نضجه، قبل أن يفقد عطراً واحداً.','We pick fruit at peak ripeness, before it loses a single aromatic note.','orchard','#F5EFE6',1],['press','نعصر على مهل','Pressed with care','نعصر الثمار برفق ونحفظ الضوء في كل قطرة.','Fruit is gently pressed to preserve its brightness.','press','#0E3B2E',2],['sip','نرشف الصفاء','Sip the moment','تصل نكهة البرتقال طازجة إلى الكأس، بلا استعجال.','Fresh orange reaches the glass, without hurry.','sip','#C6F135',3]] as [$key,$ar,$en,$bodyAr,$bodyEn,$mediaKey,$color,$order]) ProductStoryPhase::updateOrCreate(['product_id'=>$orange->id,'phase_key'=>$key], ['media_id'=>$media[$mediaKey]->id,'title_ar'=>$ar,'title_en'=>$en,'body_ar'=>$bodyAr,'body_en'=>$bodyEn,'background_color'=>$color,'phase_order'=>$order]);
            SiteSetting::updateOrCreate(['setting_key'=>'contact'], ['setting_value'=>['whatsapp'=>'966500000000','email'=>'hello@rashfa.test'],'description_ar'=>'قنوات الاتصال الظاهرة للزوار.','is_public'=>true]);
            SiteSetting::updateOrCreate(['setting_key'=>'newsletter'], ['setting_value'=>['title'=>'ادخل إلى القائمة الهادئة','subtitle'=>'رسالة موسمية حين تستحق النكهة أن تُحكى.'],'description_ar'=>'محتوى دعوة البريد أسفل الصفحة.','is_public'=>true]);
        });
    }
}
