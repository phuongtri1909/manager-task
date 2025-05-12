<?php

namespace App\Http\Controllers\Party;

use App\Helpers\SystemDefine;
use App\Http\Controllers\Controller;
use App\Http\Requests\PartyDocumentRequest;
use App\Models\PartyDocument;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class DocumentController extends Controller
{
    public string $title = 'Document';

    public function __construct()
    {
        $this->featureSlug = SystemDefine::PARTY_TEXT_FEATURE;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        
        $time               = Carbon::createFromFormat('Y', $request->year ?? now()->format('Y'));          
      
        $documentList   = PartyDocument::with('documents')->whereYear('time', $time)->active();
        $documentList   = $documentList->get();

        $dataTableData  = $this->generateDataTableData();
        return $this->view('party.documents.index', compact('dataTableData', 'documentList'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return $this->view('party.documents.form');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PartyDocumentRequest $request)
    {
        $document       = new PartyDocument();
        $document->fill($request->validated());
        $document->time = Carbon::createFromFormat('d/m/Y', $request->time);
        $document->save();
        $document->documents()->save(store_file(
            'public/documents/parties/',
            $request->file('file'),
            $request->name,
            $request->description
        ));

        flash_message(SystemDefine::CREATE_SUCCESS_MESSAGE);
        return redirect()->route('party.documents.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PartyDocument  $document
     * @return \Illuminate\Http\Response
     */
    public function edit(PartyDocument $document)
    {
        return $this->view('party.documents.form', compact('document'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PartyDocument  $document
     * @return \Illuminate\Http\Response
     */
    public function update(PartyDocumentRequest $request, PartyDocument $document)
    {
        $document->fill($request->validated());
        $document->time = Carbon::createFromFormat('d/m/Y', $request->time);
        $document->save();

        if ($request->file('file') && $request->file('file') instanceof UploadedFile) {
            $document->documents()->delete();
            $document->documents()->save(store_file(
                'public/documents/parties/',
                $request->file('file'),
                $request->name,
                $request->description
            ));
        }

        flash_message(SystemDefine::UPDATE_SUCCESS_MESSAGE);
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PartyDocument  $document
     * @return \Illuminate\Http\Response
     */
    public function destroy(PartyDocument $document)
    {
        $document->delete();

        flash_message(SystemDefine::DELETE_SUCCESS_MESSAGE);
        return redirect()->route('party.documents.index');
    }

    private function generateDataTableData(): array
    {
        $heads = [
            'STT',
            'Tên văn bản',
            'Chiều văn bản',
            'Loại văn bản',
            'Ngày văn bản',
            'Mô tả',
            [
                'label' => __('Hành động'),
                'no-export' => true, 'width' => 10
            ]
        ];

        $config = [
            'order'     => [[0, 'ASC']],
            'columns'   => array_merge(
                [['type' => 'num']],
                array_fill(0, 5, null),
                [['orderable' => false]]
            ),
            'language'  => [
                'url'   => asset('vendor/vi.json'),
            ],
            "scrollX" => true,
        ];

        return ['config' => $config, 'heads' => $heads];
    }
}
