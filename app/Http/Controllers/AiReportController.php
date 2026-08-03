<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AiReport\AiReportOrchestrator;
use Exception;
use Illuminate\Support\Facades\Log;

class AiReportController extends Controller
{
    protected AiReportOrchestrator $orchestrator;

    public function __construct(AiReportOrchestrator $orchestrator)
    {
        $this->orchestrator = $orchestrator;
    }

    public function index()
    {
        return view('modules.ai-reports.index');
    }

    public function generate(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string|max:500',
        ]);

        $userPrompt = $request->input('prompt');

        try {
            $result = $this->orchestrator->generateReport($userPrompt);

            return response()->json([
                'success' => true,
                'html' => $result['html'],
                'sql' => $result['sql'],
                'data' => $result['data'],
                'title' => $result['title'],
            ]);

        } catch (Exception $e) {
            Log::error('Error en AiReportController: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Lo siento, ocurrió un error al procesar tu solicitud: ' . $e->getMessage()
            ], 500);
        }
    }
}
