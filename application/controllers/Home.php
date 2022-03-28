<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Home extends CI_Controller
{

  public function __construct()
  {
    parent::__construct();
    $this->load->model('Migrate');
  }


  public function index()
  {
    // koneksi manual untuk mengambil list database
    $db = new PDO('mysql:host=localhost;dbname=mysql', 'root', '');
    $dbs = $db->query('SHOW DATABASES');

    while (($db = $dbs->fetchColumn(0)) !== false) {
      $databases[] = $db;
    };

    $data['dbs'] = $databases;

    $this->load->view('home_v', $data);
  }

  public function getTables()
  {
    $db1 = $_POST['dbs1'];
    $db2 = $_POST['dbs2'];

    $this->db1 = $this->load->database($db1, true);
    $this->db2 = $this->load->database($db2, true);

    $data['hasilDB1'] = $this->db1->query('SHOW TABLES')->result_array();
    $data['hasilDB2'] = $this->db2->query('SHOW TABLES')->result_array();

    echo json_encode($data);
  }

  public function getAttr()
  {
    $database1 = $_POST['dbs1'];
    $database2 = $_POST['dbs2'];
    $table1 = $_POST['tb1'];
    $table2 = $_POST['tb2'];


    $hasilAttr['attr1'] = $this->Migrate->describeTable1($database1, $table1);
    $hasilAttr['attr2'] = $this->Migrate->describeTable2($database2, $table2);

    echo json_encode($hasilAttr);
  }

  public function importDB()
  {
    $post = $this->input->post();

    // database dari element select
    $db1 = $post['databases1'];
    $db2 = $post['databases2'];

    // tabel dari element select
    $tb1 = $post['tables1'];
    $tb2 = $post['tables2'];

    // mengambil atribut tabel lama dari element select yang sudah dipilih
    $count = $this->input->post('count1');
    for ($x = 1; $x <= $count; $x++) {
      $field1[] = $this->input->post('attr' . $x, true);
    }

    // mengambil atribut tabel baru
    for ($y = 1; $y <= $count; $y++) {
      $field2[] = $this->input->post('attrBaru' . $y, true);
    }

    // mengambil data(value) atribut dari database lama
    $dataAttr = $this->Migrate->loadDB1($db1, $tb1, $field1);

    // menyocokan atribut database lama dengan atribut yang telah dipilih melalui element select
    foreach ($field1 as $f1) {
      foreach ($dataAttr as $key) {
        $newData1[] = $key[$f1];
      }
      $data1[] =  $newData1;
      $newData1 = array();
    }

    // memasukkan atribut lama kedalam atribut baru dalam bentuk array dua dimensi
    // array tampung
    $c = array();
    for ($i = 0; $i <= count($data1); $i++) {
      for ($j = 0; $j < $count; $j++) {
        $c[$field2[$j]] = $data1[$j][$i];
      }
      $hasilAttr[] = $c;
      $c = array();
    }

    // menyimpan kedalam database baru
    $this->Migrate->import($db2, $tb2, $hasilAttr);

    // kembali ke home
    redirect('home');
  }
}
  
  /* End of file Home.php */
