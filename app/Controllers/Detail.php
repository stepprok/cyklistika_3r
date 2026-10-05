<?php

namespace App\Controllers;

use App\Models\Nice;
use App\Models\RaceModel;
use App\Models\Stage;
use App\Models\Result;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\View\Table;
use Psr\Log\LoggerInterface;
use Override;

class Detail extends BaseController
{
    protected $niceModel;
    protected $stage;
    protected $raceModel;
    protected $resultModel;

    #[Override]
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->niceModel = new Nice();
        $this->stage = new Stage();
        $this->raceModel = new RaceModel();
        $this->resultModel = new Result();
    }
    
    public function detail($raceYearId)
    {
        $raceYear = $this->niceModel->find($raceYearId);

        if (!$raceYear) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Ročník nebyl nalezen.');
        }

        $stages = $this->stage->select('
            fin_stage.*, 
            fin_parcour_type.name as parcour_name,
            fin_rider.first_name as winner_first,
            fin_rider.last_name as winner_last,
            fin_rider.photo as winner_photo
        ')
            ->join('fin_parcour_type', 'fin_stage.parcour_type = fin_parcour_type.id', 'left')
            ->join('fin_result', 'fin_result.id_stage = fin_stage.id AND fin_result.rank = 1 AND fin_result.type_result = 1', 'left')
            ->join('fin_rider', 'fin_result.id_rider = fin_rider.id', 'left')
            ->where('fin_stage.id_race_year', $raceYearId)
            ->orderBy('fin_stage.number', 'ASC')
            ->findAll();

        $data = [
            'data_nice' => $raceYear,
            'stages'    => $stages
        ];

        return view('detail', $data);
    }

    public function stageResult($stageId, $typeResult)
    {
        $stage = $this->stage->find($stageId);
        if (!$stage) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Etapa nebyla nalezena.');
        }

        $results = $this->resultModel->select('
            fin_result.*, 
            fin_rider.first_name, 
            fin_rider.last_name, 
            fin_rider.photo
        ')
            ->join('fin_rider', 'fin_result.id_rider = fin_rider.id', 'left')
            ->where('fin_result.id_stage', $stageId)
            ->where('fin_result.type_result', $typeResult)
            ->orderBy('fin_result.rank', 'ASC')
            ->get()
            ->getResult();

        $data = [
            'stage'      => $stage,
            'results'    => $results,
            'typeResult' => $typeResult
        ];

        return view('stage_result', $data);
    }
}
