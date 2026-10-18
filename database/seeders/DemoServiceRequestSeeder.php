<?php

namespace Database\Seeders;

use App\Models\ServiceRequest;
use Illuminate\Database\Seeder;

class DemoServiceRequestSeeder extends Seeder
{
    public function run(): void
    {
        if (ServiceRequest::query()->exists()) {
            return;
        }

        $samples = [
            ['Amina Mwangi', 'Street Lighting', 'Kilimani, Block 4', 'Streetlight at the corner has been out for several nights, making the crossing difficult to see.', 'In Progress', 2],
            ['David Otieno', 'Water & Sanitation', 'Westlands, Parklands Road', 'Water is leaking from a pipe near the public footpath.', 'Pending', 1],
            ['Grace Wanjiku', 'Waste Collection', 'South B, Mukoma Estate', 'The shared collection point has not been emptied this week.', 'Resolved', 5],
            ['Brian Kiptoo', 'Roads & Transport', 'Ngara, Ngara Road', 'A pothole has developed beside the bus stop and is difficult to avoid.', 'Pending', 3],
        ];

        foreach ($samples as [$citizen, $category, $location, $description, $status, $daysAgo]) {
            $request = ServiceRequest::create([
                'citizen_name' => $citizen,
                'category' => $category,
                'location' => $location,
                'description' => $description,
                'status' => $status,
            ]);
            $request->update([
                'request_number' => 'CD-'.str_pad((string) $request->id, 6, '0', STR_PAD_LEFT),
            ]);
            $request->created_at = now()->subDays($daysAgo);
            $request->saveQuietly();
        }
    }
}
