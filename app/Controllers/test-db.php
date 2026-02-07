<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use Config\Database;

class TestDb extends Controller
{
    public function testDb()
    {
        try {
            $db = Database::connect();

            if ($db->connID) {
                return "✅ Koneksi PostgreSQL BERHASIL";
            }

            return "❌ Koneksi gagal";
        } catch (\Throwable $e) {
            return "❌ Error: " . $e->getMessage();
        }
    }
}
