<?php

namespace Database\Seeders;

use App\Models\Gallery;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GallerySeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $items = [
                // John Doe - Handyman
                'john.doe@example.com' => [
                    [
                        'image'       => 'https://placehold.co/800x600?text=Shelf+Installation',
                        'title'       => 'Floating Shelf Installation',
                        'description' => 'Installed 6 custom floating shelves in a living room. Measured, drilled, and mounted with hidden brackets for a clean finish.',
                        'price'       => 120.00,
                    ],
                    [
                        'image'       => 'https://placehold.co/800x600?text=Door+Repair',
                        'title'       => 'Door Frame Repair',
                        'description' => 'Repaired a damaged door frame and rehung the door for smooth operation. Included sanding and repainting.',
                        'price'       => 200.00,
                    ],
                    [
                        'image'       => 'https://placehold.co/800x600?text=TV+Mounting',
                        'title'       => 'TV Wall Mounting',
                        'description' => 'Mounted a 65-inch TV on a brick wall with cable management. Full tilt bracket included.',
                        'price'       => 90.00,
                    ],
                ],

                // Jane Smith - Cleaner
                'jane.smith@example.com' => [
                    [
                        'image'       => 'https://placehold.co/800x600?text=Kitchen+Deep+Clean',
                        'title'       => 'Kitchen Deep Clean',
                        'description' => 'Complete kitchen cleaning including appliances, cabinets, and floor. All surfaces sanitized and organized.',
                        'price'       => 150.00,
                    ],
                    [
                        'image'       => 'https://placehold.co/800x600?text=Move+Out+Clean',
                        'title'       => 'Move-Out Full House Clean',
                        'description' => 'End-of-tenancy clean for a 3-bedroom house. Carpets, bathrooms, kitchen, and windows all done to landlord standard.',
                        'price'       => 350.00,
                    ],
                    [
                        'image'       => 'https://placehold.co/800x600?text=Office+Clean',
                        'title'       => 'Office Deep Clean',
                        'description' => 'Weekly deep clean for a small office space. Desks, floors, restrooms, and common areas covered.',
                        'price'       => 180.00,
                    ],
                ],

                // Michael Johnson - Electrician
                'michael.johnson@example.com' => [
                    [
                        'image'       => 'https://placehold.co/800x600?text=Panel+Upgrade',
                        'title'       => 'Electrical Panel Upgrade',
                        'description' => 'Upgraded a 100A panel to 200A service to support a home addition. Fully permitted and inspected.',
                        'price'       => 1800.00,
                    ],
                    [
                        'image'       => 'https://placehold.co/800x600?text=EV+Charger',
                        'title'       => 'EV Charger Installation',
                        'description' => 'Installed a Level 2 EV charging station in a residential garage. Dedicated 240V circuit with proper conduit routing.',
                        'price'       => 650.00,
                    ],
                    [
                        'image'       => 'https://placehold.co/800x600?text=Lighting+Install',
                        'title'       => 'Recessed Lighting Install',
                        'description' => 'Installed 12 LED recessed lights in a kitchen and living room. Includes dimmer switches and clean drywall patching.',
                        'price'       => 480.00,
                    ],
                ],

                // Emily Brown - Painter
                'emily.brown@example.com' => [
                    [
                        'image'       => 'https://placehold.co/800x600?text=Living+Room+Paint',
                        'title'       => 'Living Room Repaint',
                        'description' => 'Full repaint of a large living room including ceilings, trim, and accent wall. Two coats of premium paint applied.',
                        'price'       => 400.00,
                    ],
                    [
                        'image'       => 'https://placehold.co/800x600?text=Exterior+Paint',
                        'title'       => 'Exterior House Painting',
                        'description' => 'Painted the full exterior of a 2-story home. Power washed, primed, and finished with weather-resistant paint.',
                        'price'       => 2200.00,
                    ],
                    [
                        'image'       => 'https://placehold.co/800x600?text=Cabinet+Paint',
                        'title'       => 'Kitchen Cabinet Refinishing',
                        'description' => 'Sanded, primed, and painted kitchen cabinets in a modern white finish. Includes new hardware installation.',
                        'price'       => 750.00,
                    ],
                ],

                // David Wilson - Plumber
                'david.wilson@example.com' => [
                    [
                        'image'       => 'https://placehold.co/800x600?text=Bathroom+Refit',
                        'title'       => 'Full Bathroom Refit',
                        'description' => 'Replaced toilet, vanity, shower unit, and all pipework in a master bathroom. Completed in 2 days.',
                        'price'       => 1500.00,
                    ],
                    [
                        'image'       => 'https://placehold.co/800x600?text=Water+Heater',
                        'title'       => 'Water Heater Replacement',
                        'description' => 'Removed old tank water heater and installed a new 50-gallon unit with updated shut-off valves.',
                        'price'       => 900.00,
                    ],
                    [
                        'image'       => 'https://placehold.co/800x600?text=Leak+Repair',
                        'title'       => 'Slab Leak Repair',
                        'description' => 'Located and repaired a slab leak under the kitchen floor. Minimal disruption with targeted jackhammering.',
                        'price'       => 1200.00,
                    ],
                ],

                // Robert Thomas - Locksmith
                'robert.thomas@example.com' => [
                    [
                        'image'       => 'https://placehold.co/800x600?text=Lock+Rekey',
                        'title'       => 'Home Rekey Service',
                        'description' => 'Rekeyed all exterior locks for a new homeowner. 4 doors completed in under an hour with new keys cut on-site.',
                        'price'       => 80.00,
                    ],
                    [
                        'image'       => 'https://placehold.co/800x600?text=Smart+Lock',
                        'title'       => 'Smart Lock Installation',
                        'description' => 'Installed a keypad smart lock on the front door with app integration and backup key access.',
                        'price'       => 150.00,
                    ],
                    [
                        'image'       => 'https://placehold.co/800x600?text=Safe+Install',
                        'title'       => 'In-Wall Safe Installation',
                        'description' => 'Installed a concealed in-wall gun safe in a bedroom closet. Includes anchoring and combination setup.',
                        'price'       => 250.00,
                    ],
                ],
            ];

            foreach ($items as $email => $galleryItems) {
                $user = User::where('email', $email)->first();

                if (!$user) {
                    $this->command->warn("User {$email} not found, skipping gallery.");
                    continue;
                }

                $profile = Profile::where('user_id', $user->id)->first();

                if (!$profile) {
                    $this->command->warn("No profile found for {$email}, skipping gallery.");
                    continue;
                }

                foreach ($galleryItems as $item) {
                    Gallery::firstOrCreate(
                        ['profile_id' => $profile->id, 'title' => $item['title']],
                        [
                            'image'       => $item['image'],
                            'description' => $item['description'],
                            'price'       => $item['price'],
                        ]
                    );
                }

                $this->command->info("Seeded gallery for: {$email}");
            }

            $this->command->info('');
            $this->command->info('✅ Gallery seeded successfully!');
        });
    }
}
