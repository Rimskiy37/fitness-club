<?php
/**
 * API: Фильтрация занятий по категории
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../src/helpers/config.php';

$category = trim($_GET['category'] ?? '');

$classModel = new ClassModel();

if ($category === '' || $category === 'all') {
    echo json_encode(['success' => true, 'classes' => $classModel->getAll()]);
} else {
    echo json_encode(['success' => true, 'classes' => $classModel->getByCategory($category)]);
}
