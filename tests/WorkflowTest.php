<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class WorkflowTest extends TestCase
{

    public function testCreateWorkflowInvalid()
    {
        $this
            ->jsonParticipant('POST', 'api/workflow')
            ->shouldReturnJson()
            ->seeJsonStructure([
                'start_at',
                'duration'
            ])
            ->seeStatusCode(422);

        $this
            ->jsonParticipant('POST', 'api/workflow', ['start_at' => date('Y-m-d')])
            ->shouldReturnJson()
            ->seeJsonStructure([
                'duration'
            ])
            ->seeStatusCode(422);

        $this
            ->jsonParticipant('POST', 'api/workflow', ['duration' => 480])
            ->shouldReturnJson()
            ->seeJsonStructure([
                'start_at'
            ])
            ->seeStatusCode(422);

        $this
            ->jsonParticipant('POST', 'api/workflow', ['start_at' => date('Y-m-d'), 'duration' => 480])
            ->shouldReturnJson()
            ->seeJsonStructure([
                'type'
            ])
            ->seeStatusCode(422);

        $this
            ->jsonParticipant('POST', 'api/workflow', ['start_at' => date('Y-m-d'), 'duration' => 480, 'type' => 'wrong'])
            ->shouldReturnJson()
            ->seeJsonStructure([
                'type'
            ])
            ->seeStatusCode(422);

        $this
            ->jsonParticipant('POST', 'api/workflow', ['start_at' => date('Y-m-d'), 'duration' => -30, 'type' => 'time_off'])
            ->shouldReturnJson()
            ->seeJsonStructure([
                'duration'
            ])
            ->seeStatusCode(422);

        $this
            ->jsonParticipant('POST', 'api/workflow', ['start_at' => date('Y-m-d'), 'duration' => 0, 'type' => 'time_off'])
            ->shouldReturnJson()
            ->seeJsonStructure([
                'duration'
            ])
            ->seeStatusCode(422);
    }

    public function testCreateWorkflow()
    {
        $this
            ->jsonParticipant('POST', 'api/workflow', ['start_at' => date('Y-m-d'), 'duration' => 480, 'type' => 'time_off'])
            ->shouldReturnJson()
            ->seeJsonEquals([
                'success' => true
            ])
            ->seeStatusCode(200);
    }

    public function testCreateWorkflowVacation()
    {
        $this
            ->jsonParticipant('POST', 'api/workflow', ['start_at' => date('Y-m-d'), 'type' => 'vacation'])
            ->shouldReturnJson()
            ->seeJsonEquals([
                'success' => true
            ])
            ->seeStatusCode(200);
    }

    public function testCreateTwoWorkflowInTheSameDay()
    {
        $this
            ->jsonParticipant('POST', 'api/workflow', ['start_at' => date('Y-m-d'), 'duration' => 480, 'type' => 'time_off']);

        $this
            ->jsonParticipant('POST', 'api/workflow', ['start_at' => date('Y-m-d'), 'duration' => 480, 'type' => 'time_off'])
            ->shouldReturnJson()
            ->seeJsonStructure([
                'start_at'
            ])
            ->seeStatusCode(422);
    }

    public function testCreateWorkflowVacationInDifferentYears()
    {
        $end_at = date('Y-m-d', strtotime(date('Y-m-d') . ' +1 year'));

        $this
            ->jsonParticipant('POST', 'api/workflow', ['start_at' => date('Y-m-d'), 'end_at' => $end_at, 'type' => 'vacation'])
            ->shouldReturnJson()
            ->seeJsonStructure([
                'start_at'
            ])
            ->seeStatusCode(422);
    }

    public function testCreateWorkflowVacationLongerThanTwoWorkingWeeks()
    {
        $end_at = date('Y-m-d', strtotime(date('Y-m-d') . ' +14 days'));

        $this
            ->jsonParticipant('POST', 'api/workflow', ['start_at' => date('Y-m-d'), 'end_at' => $end_at, 'type' => 'vacation'])
            ->shouldReturnJson()
            ->seeJsonStructure([
                'start_at'
            ])
            ->seeStatusCode(422);
    }

    public function testCreateWorkflowWithInvalidDates()
    {
        $this
            ->jsonParticipant('POST', 'api/workflow', ['start_at' => date('Y.m.d'), 'duration' => 480, 'type' => 'time_off'])
            ->shouldReturnJson()
            ->seeJsonStructure([
                'start_at'
            ])
            ->seeStatusCode(422);
    }
}
