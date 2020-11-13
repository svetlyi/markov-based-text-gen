<?php

declare(strict_types=1);

namespace TextGen;

/**
 * Builds models like:
 * [
 *     1 => [
 *         'START' => ['start_word1', 'start_word2', ...],
 *         'END' => ['end_word1', 'end_word2', ...],
 *         'MODEL' => [
 *             'start_word1' => ['word' => 2, 'some' => 1],
 *             'word' => ['hello' => 1],
 *         ],
 *     ],
 *     ...
 * ]
 */
final class Model
{
    public const START_WORD = 'START';
    public const END_WORD = 'END';
    public const MODEL_KEY = 'MODEL';
    /**
     * @var array - a model, consisting of several parts (each part for every amount of words together)
     */
    private array $model = [];
    private int $maxWordsTogether;

    /**
     * Model constructor.
     * @param int $maxWordsTogether - how many words to stick together to learn. If there are two words,
     * then the sentence "generally we are not substituting subtype" would be split into parts with two words
     * "generally we", "are not", "substituting subtype".
     */
    public function __construct(int $maxWordsTogether = 3)
    {
        $this->maxWordsTogether = $maxWordsTogether;
    }

    /**
     * Learn model from a sentence. The more sentences it learns, the "smarter" it is.
     * @param string $sentence
     */
    public function learnFromSentence(string $sentence): void
    {
        $sentence = preg_replace('/[,.!?\t\n\r\0\x0B\d]/', '', trim(mb_strtolower($sentence)));
        $words = array_values(array_filter($this->splitIntoWords($sentence), fn($w) => $w !== ''));
        for ($wordsTogether = 1; $wordsTogether <= $this->maxWordsTogether; $wordsTogether++) {
            $parts = array_map(fn($v) => implode(' ', $v), array_chunk($words, $wordsTogether));
            $this->learnFromParts($parts, $wordsTogether);
        }
    }

    /**
     * @param int $count - amount of parts, that the model consists of.
     * @return string
     */
    public function generateSentence(int $count): string
    {
        $sentence = [];
        $sentence[] = $this->getStart();
        for ($i = 1; $i <= $count; $i++) {
            if ($i === $count) {
                $sentence[] = $this->getEnd();
            } else {
                $sentence[] = $this->getNext($sentence[$i - 1]);
            }
        }

        return implode(' ', $sentence);
    }

    /**
     * Get the most possible word to start a sentence.
     */
    private function getStart(): string
    {
        return $this->getRandStrFromDistribution($this->getRandModel()[self::START_WORD]);
    }

    /**
     * Get the most possible word to end a sentence.
     */
    private function getEnd(): string
    {
        return $this->getRandStrFromDistribution($this->getRandModel()[self::END_WORD]);
    }

    /**
     * Get next word.
     *
     * @param string $after - after word
     * @return string
     */
    private function getNext(string $after): string
    {
        $randModel = $this->getRandModel()[self::MODEL_KEY];
        if (isset($randModel[$after])) {
            return $this->getRandStrFromDistribution($randModel[$after]);
        }
        foreach ($this->model as $wordsTogether => $model) {
            if (isset($model[$after])) {
                return $this->getRandStrFromDistribution($model[$after]);
            }
        }

        $wordsFromOneWordModel = array_keys($this->model[1][self::MODEL_KEY]);

        return $wordsFromOneWordModel[array_rand($wordsFromOneWordModel)];
    }

    private function splitIntoWords(string $str): array
    {
        return preg_split('/[ ,-.]/', $str);
    }

    private function learnFromParts(array $parts, int $wordsTogether): void
    {
        if (!isset($this->model[$wordsTogether])) {
            $this->model[$wordsTogether] = [];
        }

        $lastPartIndex = count($parts) - 1;
        for ($i = 0; $i <= $lastPartIndex; $i++) {
            if ($i === 0) {
                $this->learnStart($parts[$i], $wordsTogether);
                continue;
            }
            $this->learnFromCurAndPrevPart($parts[$i], $parts[$i - 1], $wordsTogether);

            if ($i === $lastPartIndex) {
                $this->learnEnd($parts[$i], $wordsTogether);
            }
        }
    }

    private function learnStart(string $start, int $wordsTogether): void
    {
        $this->learnFlat($start, $wordsTogether, self::START_WORD);
    }

    private function learnFlat(string $str, int $wordsTogether, string $modelPart): void
    {
        if (!isset($this->model[$wordsTogether][$modelPart])) {
            $this->model[$wordsTogether][$modelPart] = [$str => 1];
        } else {
            if (!isset($this->model[$wordsTogether][$modelPart][$str])) {
                $this->model[$wordsTogether][$modelPart][$str] = 1;
            } else {
                $this->model[$wordsTogether][$modelPart][$str]++;
            }
        }
    }

    private function learnEnd(string $end, int $wordsTogether): void
    {
        $this->learnFlat($end, $wordsTogether, self::END_WORD);
    }

    private function learnFromCurAndPrevPart(string $cur, string $prev, int $wordsTogether): void
    {
        if (!isset($this->model[$wordsTogether][self::MODEL_KEY])) {
            $this->model[$wordsTogether][self::MODEL_KEY] = [];
        }
        $model = $this->model[$wordsTogether][self::MODEL_KEY];

        if (!isset($model[$prev])) {
            $model[$prev] = [$cur => 1];
        } else {
            if (!isset($model[$prev][$cur])) {
                $model[$prev][$cur] = 1;
            } else {
                $model[$prev][$cur]++;
            }
        }

        $this->model[$wordsTogether][self::MODEL_KEY] = $model;
    }

    private function getRandStrFromDistribution(array $distribution): string
    {
        $wordsSets = [];

        foreach ($distribution as $word => $count) {
            $wordsSets[] = array_fill(0, $count, $word);
        }

        $totalWordsDistribution = array_merge(...$wordsSets);

        return $totalWordsDistribution[array_rand($totalWordsDistribution)];
    }

    private function getRandModel(): array
    {
        return $this->model[array_rand($this->model)];
    }

    /**
     * Restore model from backup.
     * @param array $model
     */
    public function restoreModel(array $model): void
    {
        $this->model = $model;
    }

    /**
     * Get raw model to store somewhere.
     * @return array
     */
    public function getRawModel(): array
    {
        return $this->model;
    }
}