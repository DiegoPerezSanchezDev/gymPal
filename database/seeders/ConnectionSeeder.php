<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Connection;

class ConnectionSeeder extends Seeder
{
    public function run(): void
    {
        $diego = User::where('username', 'diegoperez')->first();
        $laura = User::where('username', 'laurag')->first();
        $carlos = User::where('username', 'carlosrun')->first();
        $ana = User::where('username', 'anamyoga')->first();
        $david = User::where('username', 'davidcalis')->first();
        $elena = User::where('username', 'elenacross')->first();

        // Diego's connections (accepted)
        Connection::create(['sender_id' => $diego->id, 'receiver_id' => $laura->id, 'status' => 'accepted']);
        Connection::create(['sender_id' => $laura->id, 'receiver_id' => $diego->id, 'status' => 'accepted']);

        Connection::create(['sender_id' => $diego->id, 'receiver_id' => $carlos->id, 'status' => 'accepted']);
        Connection::create(['sender_id' => $carlos->id, 'receiver_id' => $diego->id, 'status' => 'accepted']);

        Connection::create(['sender_id' => $diego->id, 'receiver_id' => $ana->id, 'status' => 'accepted']);
        Connection::create(['sender_id' => $ana->id, 'receiver_id' => $diego->id, 'status' => 'accepted']);

        // Pending connection (Diego sent to David)
        Connection::create(['sender_id' => $diego->id, 'receiver_id' => $david->id, 'status' => 'pending']);

        // Other users' connections
        Connection::create(['sender_id' => $laura->id, 'receiver_id' => $elena->id, 'status' => 'accepted']);
        Connection::create(['sender_id' => $elena->id, 'receiver_id' => $laura->id, 'status' => 'accepted']);

        Connection::create(['sender_id' => $carlos->id, 'receiver_id' => $ana->id, 'status' => 'accepted']);
        Connection::create(['sender_id' => $ana->id, 'receiver_id' => $carlos->id, 'status' => 'accepted']);

        $this->command->info('✅ Connections created successfully!');
    }
}
