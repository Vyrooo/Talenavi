<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all()->keyBy('slug');

        $products = [
            // Electronics (7 products)
            [
                'category_slug' => 'electronics',
                'title' => 'Wireless Noise-Canceling Headphones',
                'description' => 'Over-ear Bluetooth headphones with active noise cancellation and 30-hour battery life.',
                'price' => 1499000.00,
                'stock' => 25,
                'image_url' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600',
            ],
            [
                'category_slug' => 'electronics',
                'title' => 'Mechanical Gaming Keyboard RGB',
                'description' => 'Tenkeyless mechanical gaming keyboard with customizable RGB backlighting and tactile blue switches.',
                'price' => 750000.00,
                'stock' => 40,
                'image_url' => 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=600',
            ],
            [
                'category_slug' => 'electronics',
                'title' => 'Ultra Slim 4K Smart Monitor 27 Inch',
                'description' => 'Crystal clear 4K IPS display with 144Hz refresh rate, HDR support, and USB-C connectivity.',
                'price' => 3850000.00,
                'stock' => 15,
                'image_url' => 'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?w=600',
            ],
            [
                'category_slug' => 'electronics',
                'title' => 'Ergonomic Wireless Mouse',
                'description' => 'Precision wireless mouse designed for all-day comfort with silent clicks and dual Bluetooth mode.',
                'price' => 299000.00,
                'stock' => 60,
                'image_url' => 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=600',
            ],
            [
                'category_slug' => 'electronics',
                'title' => 'Smartwatch Fitness Tracker Pro',
                'description' => 'All-day health monitoring, heart rate sensor, sleep tracking, SpO2, and 5ATM water resistance.',
                'price' => 1120000.00,
                'stock' => 30,
                'image_url' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600',
            ],
            [
                'category_slug' => 'electronics',
                'title' => 'Portable Bluetooth Speaker IPX7',
                'description' => 'Rugged waterproof speaker with 360-degree deep bass sound and 12-hour playtime.',
                'price' => 450000.00,
                'stock' => 50,
                'image_url' => 'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=600',
            ],
            [
                'category_slug' => 'electronics',
                'title' => 'Fast Charging Power Bank 20000mAh',
                'description' => 'High-capacity external battery pack with 65W PD fast charging for laptops and smartphones.',
                'price' => 380000.00,
                'stock' => 45,
                'image_url' => 'https://images.unsplash.com/photo-1609592426868-80e9276d165f?w=600',
            ],

            // Fashion (7 products)
            [
                'category_slug' => 'fashion',
                'title' => 'Classic Cotton Crewneck T-Shirt',
                'description' => '100% premium combed cotton t-shirt with breathable fabric and modern relaxed fit.',
                'price' => 125000.00,
                'stock' => 100,
                'image_url' => 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=600',
            ],
            [
                'category_slug' => 'fashion',
                'title' => 'Slim Fit Denim Jeans Dark Blue',
                'description' => 'Stretch denim trousers with durable stitching, classic 5-pocket styling, and comfortable fit.',
                'price' => 389000.00,
                'stock' => 50,
                'image_url' => 'https://images.unsplash.com/photo-1542272604-780c96856592?w=600',
            ],
            [
                'category_slug' => 'fashion',
                'title' => 'Minimalist Canvas Sneakers',
                'description' => 'Versatile everyday low-top canvas sneakers with vulcanized rubber sole and soft cushion insole.',
                'price' => 320000.00,
                'stock' => 35,
                'image_url' => 'https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?w=600',
            ],
            [
                'category_slug' => 'fashion',
                'title' => 'Water-Resistant Commuter Backpack',
                'description' => 'Lightweight city backpack with 15.6-inch padded laptop compartment and anti-theft zipper.',
                'price' => 450000.00,
                'stock' => 30,
                'image_url' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=600',
            ],
            [
                'category_slug' => 'fashion',
                'title' => 'Polarized Classic Aviator Sunglasses',
                'description' => 'UV400 protection polarized lenses with lightweight stainless steel metallic frame.',
                'price' => 210000.00,
                'stock' => 40,
                'image_url' => 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?w=600',
            ],
            [
                'category_slug' => 'fashion',
                'title' => 'Genuine Leather Bifold Wallet',
                'description' => 'Handcrafted top-grain cowhide leather wallet with RFID blocking and 8 card slots.',
                'price' => 275000.00,
                'stock' => 50,
                'image_url' => 'https://images.unsplash.com/photo-1627123424574-724758594e93?w=600',
            ],
            [
                'category_slug' => 'fashion',
                'title' => 'Unisex Heavyweight Fleece Hoodie',
                'description' => 'Warm and cozy brushed fleece pullover hoodie with kangaroo pocket and adjustable hood.',
                'price' => 295000.00,
                'stock' => 45,
                'image_url' => 'https://images.unsplash.com/photo-1556905055-8f358a7a47b2?w=600',
            ],

            // Food & Beverages (7 products)
            [
                'category_slug' => 'food-beverages',
                'title' => 'Single Origin Arabica Coffee Beans 250g',
                'description' => 'Freshly medium-roasted specialty coffee beans with floral aroma and notes of citrus chocolate.',
                'price' => 95000.00,
                'stock' => 80,
                'image_url' => 'https://images.unsplash.com/photo-1559056199-641a0ac8b55e?w=600',
            ],
            [
                'category_slug' => 'food-beverages',
                'title' => 'Japanese Ceremonial Grade Matcha Powder 100g',
                'description' => 'Authentic finely ground green tea matcha powder from Uji, Kyoto. Rich in antioxidants.',
                'price' => 165000.00,
                'stock' => 40,
                'image_url' => 'https://images.unsplash.com/photo-1576092768241-dec231879fc3?w=600',
            ],
            [
                'category_slug' => 'food-beverages',
                'title' => 'Organic Multigrain Granola Clusters 500g',
                'description' => 'Crunchy baked rolled oats with sliced almonds, chia seeds, and wild honey sweetener.',
                'price' => 78000.00,
                'stock' => 60,
                'image_url' => 'https://images.unsplash.com/photo-1517093708103-6384feed0309?w=600',
            ],
            [
                'category_slug' => 'food-beverages',
                'title' => 'Cold Pressed Extra Virgin Olive Oil 500ml',
                'description' => 'First cold pressed Mediterranean olive oil, ideal for fresh salads and healthy gourmet cooking.',
                'price' => 135000.00,
                'stock' => 50,
                'image_url' => 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?w=600',
            ],
            [
                'category_slug' => 'food-beverages',
                'title' => 'Artisan Dark Chocolate Bar 85% Cacao',
                'description' => 'Rich bean-to-bar dark chocolate made with organic Indonesian cacao nibs.',
                'price' => 45000.00,
                'stock' => 120,
                'image_url' => 'https://images.unsplash.com/photo-1549007994-cb92caebd54b?w=600',
            ],
            [
                'category_slug' => 'food-beverages',
                'title' => 'Natural Raw Forest Honey 650g',
                'description' => '100% pure raw unheated honey harvested sustainably from natural forest beehives.',
                'price' => 110000.00,
                'stock' => 45,
                'image_url' => 'https://images.unsplash.com/photo-1587049352846-4a222e784d38?w=600',
            ],
            [
                'category_slug' => 'food-beverages',
                'title' => 'Assorted Herbal Tea Bags Collection 20s',
                'description' => 'Relaxing caffeine-free infusion set featuring Chamomile, Peppermint, Lemongrass, and Lavender.',
                'price' => 62000.00,
                'stock' => 70,
                'image_url' => 'https://images.unsplash.com/photo-1597481499750-3e6b22637e12?w=600',
            ],

            // Home & Living (7 products)
            [
                'category_slug' => 'home-living',
                'title' => 'Ultrasonic Essential Oil Aroma Diffuser 500ml',
                'description' => 'Quiet ultrasonic cool mist humidifier with 7 LED ambient colors and auto shut-off function.',
                'price' => 249000.00,
                'stock' => 35,
                'image_url' => 'https://images.unsplash.com/photo-1608571423902-eed4a5ad8108?w=600',
            ],
            [
                'category_slug' => 'home-living',
                'title' => 'Nordic Ceramic Mug and Coaster Set',
                'description' => 'Handmade matte glaze ceramic coffee mug with wooden coaster, 350ml capacity.',
                'price' => 89000.00,
                'stock' => 75,
                'image_url' => 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?w=600',
            ],
            [
                'category_slug' => 'home-living',
                'title' => 'Memory Foam Ergonomic Lumbar Cushion',
                'description' => 'High-density memory foam back support cushion designed for office chairs and car seats.',
                'price' => 185000.00,
                'stock' => 50,
                'image_url' => 'https://images.unsplash.com/photo-1584100936595-c0654b55a2e2?w=600',
            ],
            [
                'category_slug' => 'home-living',
                'title' => 'Scented Soy Candle Lavender & Vanilla',
                'description' => 'Eco-friendly hand-poured 100% soy wax scented candle with crackling wood wick, burns for 45 hours.',
                'price' => 99000.00,
                'stock' => 65,
                'image_url' => 'https://images.unsplash.com/photo-1603006905003-be475563bc59?w=600',
            ],
            [
                'category_slug' => 'home-living',
                'title' => 'Double Wall Vacuum Insulated Tumbler 750ml',
                'description' => 'Stainless steel thermal water bottle keeps drinks ice-cold for 24 hours or hot for 12 hours.',
                'price' => 165000.00,
                'stock' => 90,
                'image_url' => 'https://images.unsplash.com/photo-1577937927133-66ef06acdf18?w=600',
            ],
            [
                'category_slug' => 'home-living',
                'title' => 'Minimalist LED Desk Lamp with Wireless Charger',
                'description' => 'Dimmable touch-control study lamp with 3 color temperatures and 10W fast wireless phone charger.',
                'price' => 310000.00,
                'stock' => 30,
                'image_url' => 'https://images.unsplash.com/photo-1534353436294-0dbd4bdac845?w=600',
            ],
            [
                'category_slug' => 'home-living',
                'title' => 'Premium Egyptian Cotton Bath Towel Set',
                'description' => 'Set of 2 ultra-plush, highly absorbent bath towels crafted from 600 GSM combed cotton.',
                'price' => 220000.00,
                'stock' => 40,
                'image_url' => 'https://images.unsplash.com/photo-1616627547584-bf28cee262db?w=600',
            ],

            // Beauty & Health (7 products)
            [
                'category_slug' => 'beauty-health',
                'title' => 'Hydrating Hyaluronic Acid Serum 30ml',
                'description' => 'Deep moisture boosting face serum with multi-molecular weight hyaluronic acid and vitamin B5.',
                'price' => 139000.00,
                'stock' => 70,
                'image_url' => 'https://images.unsplash.com/photo-1620916566398-39f1143ab7be?w=600',
            ],
            [
                'category_slug' => 'beauty-health',
                'title' => 'Broad Spectrum Sunscreen SPF 50+ PA++++',
                'description' => 'Lightweight, invisible gel sunscreen with zero white cast and sweat-resistant formula.',
                'price' => 119000.00,
                'stock' => 85,
                'image_url' => 'https://images.unsplash.com/photo-1556228720-195a672e8a03?w=600',
            ],
            [
                'category_slug' => 'beauty-health',
                'title' => 'Gentle Low pH Facial Cleanser 150ml',
                'description' => 'Soothing daily foam cleanser with tea tree extract and centella asiatica for sensitive skin.',
                'price' => 89000.00,
                'stock' => 90,
                'image_url' => 'https://images.unsplash.com/photo-1556228722-d0b5de70b774?w=600',
            ],
            [
                'category_slug' => 'beauty-health',
                'title' => 'Rosemary & Biotin Hair Growth Oil 100ml',
                'description' => 'Nourishing scalp treatment oil infused with organic rosemary, mint, and biotin to strengthen hair roots.',
                'price' => 145000.00,
                'stock' => 50,
                'image_url' => 'https://images.unsplash.com/photo-1608248597359-543501a3514a?w=600',
            ],
            [
                'category_slug' => 'beauty-health',
                'title' => 'Exfoliating Sea Salt Body Scrub 250g',
                'description' => 'Invigorating mineral body polish infused with sweet almond oil and uplifting grapefruit essence.',
                'price' => 98000.00,
                'stock' => 60,
                'image_url' => 'https://images.unsplash.com/photo-1567928815104-b698d4c982c2?w=600',
            ],
            [
                'category_slug' => 'beauty-health',
                'title' => 'Niacinamide 10% + Zinc 1% Blemish Solution',
                'description' => 'Targeted serum that helps balance visible sebum activity and visibly brighten uneven skin tone.',
                'price' => 125000.00,
                'stock' => 65,
                'image_url' => 'https://images.unsplash.com/photo-1601049541289-9b1b7bbbfe19?w=600',
            ],
            [
                'category_slug' => 'beauty-health',
                'title' => 'Moisturizing Shea Butter Lip Balm 15g',
                'description' => 'Deeply conditioning lip balm with organic shea butter, beeswax, and antioxidant vitamin E.',
                'price' => 39000.00,
                'stock' => 150,
                'image_url' => 'https://images.unsplash.com/photo-1599305090598-fe179d501227?w=600',
            ],
        ];

        foreach ($products as $item) {
            $category = $categories->get($item['category_slug']);
            if (! $category) {
                continue;
            }

            Product::updateOrCreate(
                ['title' => $item['title']],
                [
                    'category_id' => $category->id,
                    'description' => $item['description'],
                    'price' => $item['price'],
                    'stock' => $item['stock'],
                    'image_url' => $item['image_url'],
                ]
            );
        }
    }
}
