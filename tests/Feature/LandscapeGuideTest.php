<?php

namespace Tests\Feature;

use App\Models\LandscapeAssessment;
use App\Support\LandscapeGuide;
use Tests\TestCase;

class LandscapeGuideTest extends TestCase
{
    public function test_guide_covers_every_form_option_exactly(): void
    {
        $fields = LandscapeGuide::fields();

        foreach (LandscapeAssessment::options() as $field => $choices) {
            $this->assertArrayHasKey($field, $fields, "Panduan belum punya kolom {$field}");
            $this->assertSame($choices, array_keys($fields[$field]['options']),
                "Pilihan panduan untuk {$field} harus sama persis dengan pilihan di form");
        }
    }

    public function test_every_cited_source_exists(): void
    {
        $sources = LandscapeGuide::sources();

        foreach (LandscapeGuide::fields() as $field => $guide) {
            foreach ($guide['sources'] as $key) {
                $this->assertArrayHasKey($key, $sources, "Sumber {$key} di kolom {$field} tidak ada");
            }
            foreach ($guide['basis'] ?? [] as $basis) {
                $this->assertArrayHasKey($basis['source'], $sources, "Sumber {$basis['source']} di dasar kolom {$field} tidak ada");
            }
        }
    }

    public function test_guide_page_is_public_and_lists_options(): void
    {
        $response = $this->get('/lanskap/panduan');

        $response->assertOk()
            ->assertSee('Panduan Pengisian Data Lanskap')
            ->assertSee('Hutan sekunder/semak belukar')
            ->assertSee('Sangat curam (> 45%)')
            ->assertSee('radius 50 meter dari setiap node')
            ->assertSee('SNI 7645:2010')
            ->assertSee('Permen PU 22/PRT/M/2007');
    }
}
