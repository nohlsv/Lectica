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
        // College mapping from old to new structure
        $collegeMapping = [
            'College of Engineering' => 'College of Engineering and Architecture',
            'College of Business' => 'College of Business and Accountancy',
            'College of Computer Studies' => 'College of Computer Studies', // No change
            'College of Technology' => 'College of Technology', // No change
            'College of Allied Health and Sciences' => 'College of Allied Health and Sciences', // No change
        ];

        // Update existing programs with new college names
        foreach ($collegeMapping as $oldCollege => $newCollege) {
            DB::table('programs')
                ->where('college', $oldCollege)
                ->update(['college' => $newCollege]);
        }

        // Add any missing programs for College of Arts and Science (if needed)
        // This is a placeholder for when Arts and Science programs are defined
        
        echo "College structure updated successfully!\n";
        echo "Old 'College of Engineering' -> New 'College of Engineering and Architecture'\n";
        echo "Old 'College of Business' -> New 'College of Business and Accountancy'\n";
        echo "Architecture moved to 'College of Engineering and Architecture'\n";
        echo "Other colleges remain unchanged.\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse the college mapping
        $reverseMapping = [
            'College of Engineering and Architecture' => 'College of Engineering',
            'College of Business and Accountancy' => 'College of Business',
            'College of Computer Studies' => 'College of Computer Studies',
            'College of Technology' => 'College of Technology',
            'College of Allied Health and Sciences' => 'College of Allied Health and Sciences',
        ];

        foreach ($reverseMapping as $newCollege => $oldCollege) {
            DB::table('programs')
                ->where('college', $newCollege)
                ->update(['college' => $oldCollege]);
        }

        echo "College structure reverted to original state.\n";
    }
};
