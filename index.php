<?php
	error_reporting(E_ALL ^E_NOTICE);
	//ini_set('max_execution_time',300);
	//ini_set('max_input_time',300);
  odbc_close_all ();
  
   // Cari, plasiyer, fiyat bilgisi vs. bilgileri sorgulamak için aktif şirketin veritabanına bağlanır.
	 $baglanti = odbc_connect('DRIVER={SQL Server};SERVER=SERVERNAME;DATABASE=NETSISCURRENTDB','sa','12345'); 	
	
	
try
{
$kernel = new COM("NETOPENX50.Kernel") or die("Unable to instantiate Netopenx50.Kernel");
$Sirket = $kernel->yenisirket(0, "SIRKETADI", "TEMELSET", "", "KULLANICIADI", "KULLANICISIFRE", 0);	
$Fatura = $kernel->yeniFatura($Sirket, 7);


//Sipariş numarası post edilmezse yeni numara oluştur.
$skod=($_POST['skod']);
if ($skod!='')
{ 
  $Fatura->Ust->FATIRS_NO = "$skod";
}
else	
{ 	
  $Fatura->Ust->FATIRS_NO = $Fatura->YeniNumara("S"); 
} 

/*
Manuel numara girişi yapılmak istenirse
//$Fatura->Ust->FATIRS_NO ="S00000000037997"; */
 
 
$CariKod="$_POST[carikodu]";
$PLA_KODU=$_POST['PLA_KODU'];

// Cari aktif fiyatgrubu veritabanından sorgulanıyor
$sorgu = odbc_exec($baglanti,"select FIYATGRUBU from TBLCASABIT WITH(NOLOCK) where CARI_KOD = '$CariKod'");
$FIYATGRUBU = odbc_fetch_object($sorgu);
$FIYATGRUBU=$FIYATGRUBU->FIYATGRUBU;


// Hazırlanacak bir frontend arayüz ile aşağıdaki veriler post edilebilir.
$stoklar=$_POST['stoklar'];
$adetler=$_POST['adetler'];
$eklan1=$_POST['eklan1'];
 

$Fatura->Ust->CariKod = "$_POST[carikodu]";
$Fatura->Ust->Tarih = date('d-m-Y');
$Fatura->Ust->FiiliTarih = date('d-m-Y');

// Ek açıklamalar bölümünde Türkçe karakter sorunları yaşamamak için
$Fatura->Ust->Aciklama = mb_convert_encoding("$_POST[aciklama]","windows-1254", "utf-8");
$Fatura->Ust->EKACK1 =mb_convert_encoding("$_POST[EKACK1]","windows-1254", "utf-8");
$Fatura->Ust->EKACK2 =mb_convert_encoding("$_POST[EKACK2]","windows-1254", "utf-8");
$Fatura->Ust->EKACK3 =mb_convert_encoding("$_POST[EKACK3]","windows-1254", "utf-8");
$Fatura->Ust->EKACK4 =mb_convert_encoding("$_POST[EKACK4]","windows-1254", "utf-8");
$Fatura->Ust->EKACK5 =mb_convert_encoding("$_POST[EKACK5]","windows-1254", "utf-8");
$Fatura->Ust->EKACK6 =mb_convert_encoding("$_POST[EKACK6]","windows-1254", "utf-8");
$Fatura->Ust->EKACK7 =mb_convert_encoding("$_POST[EKACK7]","windows-1254", "utf-8");
$Fatura->Ust->EKACK8 =mb_convert_encoding("$_POST[EKACK8]","windows-1254", "utf-8");
$Fatura->Ust->EKACK9 =mb_convert_encoding("$_POST[EKACK9]","windows-1254", "utf-8");
$Fatura->Ust->EKACK10 =mb_convert_encoding("$_POST[EKACK10]","windows-1254", "utf-8");
$Fatura->Ust->EKACK11 =mb_convert_encoding("$_POST[EKACK11]","windows-1254", "utf-8");
$Fatura->Ust->EKACK12 =mb_convert_encoding("$_POST[EKACK12]","windows-1254", "utf-8");
$Fatura->Ust->EKACK13 =mb_convert_encoding("$_POST[EKACK13]","windows-1254", "utf-8");
$Fatura->Ust->EKACK14 =mb_convert_encoding("$_POST[EKACK14]","windows-1254", "utf-8");
$Fatura->Ust->EKACK15 =mb_convert_encoding("$_POST[EKACK15]","windows-1254", "utf-8");
$Fatura->Ust->EKACK16 =mb_convert_encoding("$_POST[EKACK16]","windows-1254", "utf-8");

$Fatura->Ust->ENTEGRE_TRH= date('d-m-Y');
$Fatura->Ust->KOD1 = "1";
$Fatura->Ust->FIYATTARIHI = date('d-m-Y');
$Fatura->Ust->SIPARIS_TEST = date('d-m-Y');
$Fatura->Ust->KOSULTARIHI =date('d-m-Y');
	

$Fatura->Ust->TIPI = 2;
$Fatura->Ust->PLA_KODU = "$_POST[plakodu]";
$Fatura->Ust->KDV_DAHILMI = false;

	
//Sipariş kalem bilgileri oluşturma	
for($i=0;$i<count($stoklar);$i++) {

    $FatKalem = $Fatura->kalemYeni ("$stoklar[$i]");
    $FatKalem->DEPO_KODU = "0";
    $FatKalem->STra_GCMIK = "$adetler[$i]"; // adet
    $FatKalem->Listeno = "1";
    $FatKalem->Olcubr =  "1";
    if ($ekalan1[$i]) $FatKalem->Ekalan1 = mb_convert_encoding("$renkler[$i]","windows-1254", "utf-8"); 
    $sorgu = odbc_exec($baglanti,"SELECT FIYAT1 FROM TBLSTOKFIAT WITH(NOLOCK) WHERE FIYATGRUBU='$FIYATGRUBU' and STOKKODU='$stoklar[$i]' and A_S = 'S'");
    $fiyat = odbc_fetch_object($sorgu);
    $FatKalem->STra_NF =number_format($fiyat->FIYAT1,5, ',', '.');
    $FatKalem->STra_BF =number_format($fiyat->FIYAT1,5, ',', '.');	
 }
 
 

	$Fatura->kayitYeni();
  $Sirket->LogOff();
	$kernel->FreeNetsisLibrary();

  //Oluşturulan sipariş kaydının nosunu getirir.
	echo $Fatura->Ust->FATIRS_NO;
  odbc_close_all();	
	 }
        catch (Exception $e)
        {
            // var_dump($e);
              echo '<br><br><br>';
              echo $e->getMessage();
              echo '<br><br><br>';
              echo $kernel->SonNetsisHata->Hata;
              echo '<br><br><br>';
              echo $kernel->SonNetsisHata->Detay;
               
        }  
		
?>
