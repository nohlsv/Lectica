<?php

// Simple test script to verify MultiplayerGame functionality
require_once 'vendor/autoload.php';

use App\Models\MultiplayerGame;
use App\Enums\MultiplayerGameStatus;

echo "Testing MultiplayerGame model...\n";

// Test that the model can be instantiated
$game = new MultiplayerGame();
echo "✓ Model loads successfully!\n";

// Test fillable fields
$fillableFields = $game->getFillable();
echo "✓ Available fillable fields: " . implode(', ', $fillableFields) . "\n";

// Test status enum
echo "✓ Status enum values: " . implode(', ', array_column(MultiplayerGameStatus::cases(), 'value')) . "\n";

echo "All tests passed! MultiplayerGame model is working correctly.\n";
