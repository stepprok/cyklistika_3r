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

class Index extends BaseController
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
    
    public function index()
    {
        $data_nice = $this->niceModel->select('race_year.*, SUM(fin_stage.distance) as total_distance')->like('race_year.real_name', 'Paris - Nice')->join('stage', 'race_year.id = stage.id_race_year', 'left')->groupBy('race_year.id')->orderBy('race_year.year', 'ASC')->findAll();

        $data = [
            'data_nice' => $data_nice
        ];

        return view('index', $data);
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

    public function createRaceYear()
    {
        $races = $this->niceModel
            ->where('sex', 'M')
            ->where('category', 'E')
            ->like('real_name', 'Paris - Nice')
            ->orderBy('real_name', 'ASC')
            ->findAll();

        $data = [
            'races' => $races
        ];

        return view('race_year_create', $data);
    }

    public function storeRaceYear()
    {
        $rules = [
            'real_name' => 'required|min_length[3]',
            'id_race'   => 'required|integer',
            'year'      => 'required|integer|exact_length[4]',
            'logo'      => 'uploaded[logo]|is_image[logo]|max_size[logo,2048]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $logoFile = $this->request->getFile('logo');
        $logoName = null;

        if ($logoFile && $logoFile->isValid() && !$logoFile->hasMoved()) {
            $logoName = $logoFile->getRandomName();
            $logoFile->move(FCPATH . 'assets/img/logos/', $logoName);
        }

        $this->niceModel->insert([
            'real_name' => $this->request->getPost('real_name'),
            'id_race'   => $this->request->getPost('id_race'),
            'year'      => $this->request->getPost('year'),
            'logo'      => $logoName
        ]);

        return redirect()->to(base_url())->with('success', 'Nový ročník závodu byl úspěšně přidán.');
    }
}
