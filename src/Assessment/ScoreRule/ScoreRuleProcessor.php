<?php

namespace Codeages\Biz\ItemBank\Assessment\ScoreRule;

use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\ItemBank\Answer\Service\AnswerQuestionReportService;

class ScoreRuleProcessor
{
    protected $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    public function review($questionResult, $rules)
    {
        foreach ($rules as $rule) {
            if (empty($this->biz['score_rule.'.$rule['name']])) {
                continue;
            }
            $ruleClass = $this->biz['score_rule.'.$rule['name']];
            $reviewResult = $ruleClass->review($questionResult, $rule['score']);
            if (!empty($reviewResult['status'])) {
                return $reviewResult;
            }
        }

        return [
            'status' => AnswerQuestionReportService::STATUS_REVIEWING,
            'score' => 0,
        ];
    }

    public function processRule($question)
    {
        $rule = [];
        $chains = $this->biz['score_rules'];
        foreach ($chains as $type => $className) {
            $class = $this->biz['score_rule.'.$type];
            $chainRule = $class->processRule($question);
            if (!empty($chainRule)) {
                $rule[] = $chainRule;
            }
        }

        return $rule;
    }

    public function setQuestionScore($question, $rules)
    {
        foreach ($rules as $rule) {
            $ruleClass = $this->biz['score_rule.'.$rule['name']];
            $question = $ruleClass->setQuestionScore($question, $rule['score']);
        }

        return $question;
    }
}
