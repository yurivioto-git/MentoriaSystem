<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;

class YearRollController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function run(bool $isDryRun = false): void
    {
        echo "Iniciando script de virada de ano...\n";
        if ($isDryRun) {
            echo "MODO DE SIMULAÇÃO (DRY RUN) - Nenhuma alteração será salva no banco.\n";
        }

        $log = [];
        $students = $this->userModel->getAll('aluno', true); // Pega todos os alunos ativos

        if (empty($students)) {
            echo "Nenhum aluno ativo encontrado para processar.\n";
            return;
        }

        $behavior = $_ENV['YEAR_ROLLOVER_BEHAVIOR'] ?? 'deactivate';
        echo "Comportamento para alunos do 3º ano: {$behavior}\n";

        foreach ($students as $student) {
            $logMessage = "Processando Aluno: {$student['nome']} (RM: {$student['rm']}) - Série Atual: {$student['serie']}";
            
            $updateData = [];
            switch ($student['serie']) {
                case 1:
                    $updateData['serie'] = 2;
                    $logMessage .= " -> Promovido para 2ª série.";
                    break;
                case 2:
                    $updateData['serie'] = 3;
                    $logMessage .= " -> Promovido para 3ª série.";
                    break;
                case 3:
                    if ($behavior === 'deactivate') {
                        $updateData['active'] = false;
                        $logMessage .= " -> Marcado como inativo (concluinte).";
                    } else { // 'graduate' ou outro
                        // Aqui poderia ser implementada outra lógica, como manter a série e mudar um status `graduado`
                        $logMessage .= " -> Nenhuma ação definida para concluintes (configuração: {$behavior}).";
                    }
                    break;
                default:
                    $logMessage .= " -> Nenhuma ação necessária.";
                    break;
            }

            if (!empty($updateData) && !$isDryRun) {
                try {
                    $this->userModel->update($student['id'], $updateData);
                } catch (\Exception $e) {
                    $logMessage .= " ERRO: " . $e->getMessage();
                }
            }
            
            echo $logMessage . "\n";
            $log[] = $logMessage;
        }

        // Salva o log em um arquivo
        $logPath = PROJECT_ROOT . '/uploads/logs';
        if (!is_dir($logPath)) {
            mkdir($logPath, 0755, true);
        }
        $logFile = $logPath . '/year_rollover_' . date('Y-m-d_H-i-s') . '.log';
        file_put_contents($logFile, implode("\n", $log));

        echo "Script finalizado. Log salvo em: {$logFile}\n";
    }
}
