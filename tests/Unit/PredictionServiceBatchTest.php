<?php

namespace Tests\Unit;

use App\Services\PredictionService;
use PHPUnit\Framework\TestCase;

class PredictionServiceBatchTest extends TestCase
{
    public function test_it_chunks_large_batch_prediction_requests(): void
    {
        $mlApiStub = new class {
            public array $calls = [];

            public function batchPredict(array $predictions): array
            {
                $this->calls[] = count($predictions);

                return [
                    'success' => true,
                    'predictions' => array_map(
                        static fn (array $prediction): array => [
                            'production_mt' => 123.45,
                            'productivity_mt_ha' => 12.34,
                            'confidence_score' => 88.8,
                            'input' => $prediction,
                        ],
                        $predictions
                    ),
                ];
            }
        };

        $service = new class($mlApiStub) extends PredictionService {
            public function __construct(private object $stub)
            {
                $this->mlApi = $stub;
            }

            public function normalizeCropName(string $crop): string
            {
                return strtoupper(trim($crop));
            }
        };

        $batchData = array_fill(0, 101, [
            'municipality' => 'BUGUIAS',
            'farm_type' => 'IRRIGATED',
            'month' => 'JAN',
            'crop' => 'LETTUCE',
            'area_harvested' => 10,
            'year' => 2026,
        ]);

        $result = $service->predictBatch($batchData);

        $this->assertSame([100, 1], $mlApiStub->calls);
        $this->assertTrue($result['success']);
        $this->assertCount(101, $result['predictions']);
        $this->assertSame(123.45, $result['predictions'][0]['production_mt']);
        $this->assertSame(123.45, $result['predictions'][100]['production_mt']);
    }
}
