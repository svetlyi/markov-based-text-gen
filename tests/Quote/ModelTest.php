<?php

declare(strict_types=1);

namespace TextGen\Tests\Quote;

use PHPUnit\Framework\TestCase;
use TextGen\Model;

class ModelTest extends TestCase
{
    public function testBuildWithOneWordFromSentence()
    {
        $sentence = 'generally we are not substituting subtype objects for supertype objects, '
            . 'we are simply using subtype objects as supertype objects';
        $model = new Model(1);
        $model->learnFromSentence($sentence);
        $rawModel = $model->getRawModel();
        $expectedModel = [
            1 => [
                Model::START_WORD => ['generally' => 1],
                Model::END_WORD => ['objects' => 1],
                Model::MODEL_KEY => [
                    'generally' => ['we' => 1],
                    'we' => ['are' => 2],
                    'are' => ['not' => 1, 'simply' => 1],
                    'not' => ['substituting' => 1],
                    'substituting' => ['subtype' => 1],
                    'objects' => ['for' => 1, 'as' => 1, 'we' => 1],
                    'for' => ['supertype' => 1],
                    'supertype' => ['objects' => 2],
                    'simply' => ['using' => 1],
                    'using' => ['subtype' => 1],
                    'subtype' => ['objects' => 2],
                    'as' => ['supertype' => 1],
                ],
            ],
        ];
        $this->assertEquals($expectedModel, $rawModel);
    }

    public function testBuildWithTwoWordsFromSentence()
    {
        $sentence = 'generally we are not substituting subtype objects for supertype objects, '
            . 'we are simply using subtype objects as supertype objects';
        $model = new Model(2);
        $model->learnFromSentence($sentence);
        $rawModel = $model->getRawModel();
        $expectedModel = [
            1 => [
                Model::START_WORD => ['generally' => 1],
                Model::END_WORD => ['objects' => 1],
                Model::MODEL_KEY => [
                    'generally' => ['we' => 1],
                    'we' => ['are' => 2],
                    'are' => ['not' => 1, 'simply' => 1],
                    'not' => ['substituting' => 1],
                    'substituting' => ['subtype' => 1],
                    'objects' => ['for' => 1, 'as' => 1, 'we' => 1],
                    'for' => ['supertype' => 1],
                    'supertype' => ['objects' => 2],
                    'simply' => ['using' => 1],
                    'using' => ['subtype' => 1],
                    'subtype' => ['objects' => 2],
                    'as' => ['supertype' => 1],
                ],
            ],
            2 => [
                Model::START_WORD => ['generally we' => 1],
                Model::END_WORD => ['objects' => 1],
                Model::MODEL_KEY => [
                    'generally we' => ['are not' => 1],
                    'are not' => ['substituting subtype' => 1],
                    'substituting subtype' => ['objects for' => 1],
                    'objects for' => ['supertype objects' => 1],
                    'supertype objects' => ['we are' => 1],
                    'we are' => ['simply using' => 1],
                    'simply using' => ['subtype objects' => 1],
                    'subtype objects' => ['as supertype' => 1],
                    'as supertype' => ['objects' => 1],
                ],
            ],
        ];
        $this->assertEquals($expectedModel, $rawModel);
    }

    public function testBuildWithOneWordFromSentenceWithNums()
    {
        $sentence = 'any entry showing that sector 123 is occupied';
        $model = new Model(1);
        $model->learnFromSentence($sentence);
        $rawModel = $model->getRawModel();
        $expectedModel = [
            1 => [
                Model::START_WORD => ['any' => 1],
                Model::END_WORD => ['occupied' => 1],
                Model::MODEL_KEY => [
                    'any' => ['entry' => 1],
                    'entry' => ['showing' => 1],
                    'showing' => ['that' => 1],
                    'that' => ['sector' => 1],
                    'sector' => ['is' => 1],
                    'is' => ['occupied' => 1],
                ],
            ],
        ];

        $this->assertEquals($expectedModel, $rawModel);
    }
}