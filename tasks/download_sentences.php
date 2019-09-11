<?php
/**
 * Serves a file containing the annotated sentences for a task, ready to be downloaded
 */
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . "/resources/config.php");
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') ."/dao/task_dao.php");
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') ."/dao/project_dao.php");
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') ."/dao/sentence_task_dao.php");
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') ."/dao/comment_dao.php");
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') ."/dto/sentence_task_dto.php");

$PAGETYPE = "user";
require_once(RESOURCES_PATH . "/session.php");

$task_id = filter_input(INPUT_GET, "task_id");


if (isset($task_id)) {
  $task_dao = new task_dao();
  $task = $task_dao->getTaskById($task_id);
  $project_dao = new project_dao();
  $project = $project_dao->getProjectById($task->project_id);
  $sentence_task_dto = new sentence_task_dto();

  if ($task->status == "DONE") {
    $sentence_task_dao = new sentence_task_dao();
    $st_array = $sentence_task_dao->getAnnotatedSentecesByTask($task->id);

    // output headers so that the file is downloaded rather than displayed

    header('Content-Encoding: UTF-8');
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename=task_' . $task->id . '-evaluation.tsv');

    // create a file pointer connected to the output stream
    $output = fopen('php://output', 'w');

    $delimiter = chr(9);
    $sample = (count($st_array) > 0) ? $st_array[0] : null;

    if (!isset($sample)) return;

    $headers  = array("Source");
    if ((isset($task->target_lang))) {
      for ($i = 0; $i < count($sample->target_text); $i++) {
        $headers[] = "Target " . ($i + 1);
      }
    }

    $headers[] = "Source lang";
    if (isset($task->target_lang)) $headers[] = "Target lang";
    $headers = array_merge($headers, array("Evaluation", "Description", "Evaluation details"));

    fputs($output, $bom = ( chr(0xEF) . chr(0xBB) . chr(0xBF) ));

    // output the column headings
    fputs($output, implode($headers, $delimiter)."\n");

    $comment_dao = new comment_dao();

    foreach ($st_array as $st) {  
      $source_text = $st->source_text;
      $target_text = $st->target_text;

      $source_text = preg_replace("/\r|\n/", "", $source_text);
      if (preg_match('/(.*)("|\t|\')(.*)/', $source_text)) {
        $source_text = '"' . $source_text . '"';
      }

      for ($i = 0; $i < count($target_text); $i++) {
        $target_text[$i] = preg_replace( "/\r|\n/", "", $target_text[$i]);
        if (preg_match('/(.*)("|\t|\')(.*)/', $target_text[$i])) {
          $target_text[$i] = '"' . $target_text[$i] . '"';
        }
      }

      // Comments
      $sentence_comments = $comment_dao->getCommentsByPair($st->id);
      $sentence_comment = array();
      foreach ($sentence_comments as $stc) {
        $sentence_comment[] = $stc->name . ": " . $stc->value;
      }

      $row = array($source_text);
      if ((isset($task->target_lang))) {
        foreach($target_text as $text) {
          $row[] = $text;
        }
      }

      $row[] = $task->source_lang;
      if (isset($task->target_lang)) $row[] = $task->target_lang;
      $row = array_merge($row, array($st->evaluation, $sentence_task_dto->getLabel($st->evaluation), implode($sentence_comment, "; ")));
      fputs($output,  implode($row, $delimiter)."\n");
    }
  }
  else{
    //The task is not done
  }
}