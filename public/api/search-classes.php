<?php
/**
 * API: Поиск занятий по названию
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../src/helpers/config.php';

$query = trim($_GET['q'] ?? '');

$classModel = new ClassModel();

if ($query === '') {
    echo json_encode(['success' => true, 'classes' => $classModel->getAll()]);
} else {
    echo json_encode(['success' => true, 'classes' => $classModel->search($query)]);
}
