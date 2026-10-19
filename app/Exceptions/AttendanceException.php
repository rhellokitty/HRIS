<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Pelanggaran aturan bisnis absensi (pesan aman ditampilkan ke pengguna).
 */
class AttendanceException extends RuntimeException
{
}
