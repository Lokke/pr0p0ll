<?php

namespace App\Services;

use App\Filament\Resources\MyPollResource\Widgets\ApexAnswerChart;
use App\Models\AnswerTypes\BoolAnswer;
use App\Models\AnswerTypes\MultipleChoiceAnswer;
use App\Models\AnswerTypes\SingleOptionAnswer;
use App\Models\Polls\MyPoll;
use App\Models\Question;
use Filament\Widgets\WidgetConfiguration;
use Illuminate\Support\Collection;

class PollResultService
{
    private MyPoll $poll;

    public function __construct(MyPoll $poll)
    {
        $this->poll = $poll;
    }

    public function getAllWidgets(): array
    {
        $widgets = [];
        $this->poll->questions->each(function (Question $question) use (&$widgets) {
            $widgets[] = $this->createResultWidget($question);
        });

        return array_filter($widgets);
    }

    private function createResultWidget(Question $question): ?WidgetConfiguration
    {
        $answerType = $question->answerType();

        return match (true) {
            $answerType instanceof SingleOptionAnswer, $answerType instanceof MultipleChoiceAnswer => $this->getBarChartWidget($question),
            $answerType instanceof BoolAnswer => $this->getBooleanChartWidget($question),
            default => null,
        };
    }

    private function getBooleanChartWidget(Question $question): WidgetConfiguration
    {
        $trueAnswersCount = $question->answers()->whereHasMorph('answerable', BoolAnswer::class, function ($query) {
            $query->where('answer_value', true);
        })->count();
        $falseAnswerCounts = $question->answers()->whereHasMorph('answerable', BoolAnswer::class, function ($query) {
            $query->where('answer_value', false);
        })->count();

        $answerData = [
            'heading' => $question->title,
            'chartId' => 'chart-'.$question->id,
            'chartOptions' => [
                'chart' => [
                    'type' => 'pie',
                    'height' => 450,
                ],
                'series' => [$trueAnswersCount, $falseAnswerCounts],
                'labels' => ['Ja', 'Nein'],
                'legend' => [
                    'labels' => [
                        'colors' => '#f2f5f4',
                        'fontWeight' => 600,
                        'fontFamily' => 'Inter',
                    ],
                ],
                'colors' => ['#5cb85c', '#ee4d2e'],
            ],
        ];

        return ApexAnswerChart::make(['answerData' => $answerData]);
    }

    private function getBarChartWidget(Question $question): WidgetConfiguration
    {
        $options = collect($question->options)->map(function ($option) {
            return $option['title'];
        });

        $answerData = [
            'heading' => $question->title,
            'chartId' => 'chart-'.$question->id,
            'chartOptions' => [

                'chart' => [
                    'type' => 'bar',
                    'height' => 450,
                    'toolbar' => [
                        'show' => false,
                    ],

                ],
                'series' => [
                    [
                        'name' => 'Antworten',
                        'data' => $this->getOptionsAnswerCounts($question, $options, get_class($question->answerType()))->values()->toArray(),
                    ],
                ],
                'grid' => [
                    'yaxis' => [
                        'lines' => [
                            'show' => false,
                        ],
                    ],
                ],
                'xaxis' => [
                    'categories' => $options->toArray(),
                    'labels' => [
                        'style' => [
                            'colors' => '#f2f5f4',
                            'fontWeight' => 600,
                            'fontFamily' => 'Inter',
                        ],
                    ],
                ],
                'yaxis' => [
                    'labels' => [
                        'style' => [
                            'colors' => '#f2f5f4',
                            'fontWeight' => 600,
                            'fontFamily' => 'Inter',
                        ],
                    ],
                ],
                'colors' => ['#ee4d2e'],
            ],
        ];

        return ApexAnswerChart::make(['answerData' => $answerData]);
    }

    private function getOptionsAnswerCounts(Question $question, Collection $options, string $answerType): Collection
    {
        $optionsAnswerCounts = [];
        $options->each(function ($option) use ($question, &$optionsAnswerCounts, $answerType) {
            $optionAnswerCount = $question->answers()->whereHasMorph('answerable', $answerType, function ($query) use ($option) {
                $query->where('answer_value', $option);
            })->count();
            $optionsAnswerCounts[$option] = $optionAnswerCount;
        });

        return collect($optionsAnswerCounts);
    }
}
