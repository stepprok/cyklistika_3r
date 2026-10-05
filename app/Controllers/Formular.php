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

class Formular extends BaseController
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
