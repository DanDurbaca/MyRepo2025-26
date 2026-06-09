<?php
  $_SESSION = [];

  session_destroy();

  header("Location: ../pages/home/");
  exit;
