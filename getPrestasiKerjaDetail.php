<?php
	$kegiatan_tugas_jabatan = [];
	$no=1;
	$cek_utama=mysqli_query($koneksi,"SELECT $bulan,breakdown_id,$database_skp.rwyt_target_kerja_tahunan.* FROM $database_skp.rwyt_breakdown_target_kerja 
				LEFT JOIN $database_skp.rwyt_target_kerja_tahunan ON $database_skp.rwyt_target_kerja_tahunan.target_id=$database_skp.rwyt_breakdown_target_kerja.target_id 
				WHERE $database_skp.rwyt_breakdown_target_kerja.pegawai_id='$j[pegawai_id]' 
				AND $database_skp.rwyt_breakdown_target_kerja.informasi_id='$l[informasi_id]' 
				AND unsur_id=1 ORDER BY $database_skp.rwyt_breakdown_target_kerja.target_id");
	$ketemu_utama=mysqli_num_rows($cek_utama);

	if ($ketemu_utama >0){
		$tampil=mysqli_query($koneksi,"SELECT $bulan,breakdown_id,$database_skp.rwyt_target_kerja_tahunan.* FROM $database_skp.rwyt_breakdown_target_kerja 
					LEFT JOIN $database_skp.rwyt_target_kerja_tahunan ON $database_skp.rwyt_target_kerja_tahunan.target_id=$database_skp.rwyt_breakdown_target_kerja.target_id
					WHERE $database_skp.rwyt_breakdown_target_kerja.pegawai_id='$j[pegawai_id]' 
					AND $database_skp.rwyt_breakdown_target_kerja.informasi_id='$l[informasi_id]' 
					AND unsur_id=1 ORDER BY $database_skp.rwyt_breakdown_target_kerja.target_id");
		
		while ($r=mysqli_fetch_array($tampil)){
			if ($r[$bulan]<>'0'){
				$record = [];
				$record['no'] = $no;
				
				$tampil2=mysqli_query($koneksi,"SELECT * FROM $database_skp.ref_jenis_kegiatan WHERE kegiatan_id='$r[kegiatan_id]'");
				$a=mysqli_fetch_array($tampil2);
				$record['jenis_kegiatan'] = $a['kegiatan'];
				
				if ($l['jabatan_id']=='1'){
					if ($r['kegiatan_id']=='1' OR $r['kegiatan_id']=='2'){
						$tugas_sql=mysqli_query($koneksi,"SELECT * from $database_skp.ref_dau_dak WHERE id='$r[program_keg_subkeg]'");
						$tugas=mysqli_fetch_array($tugas_sql);
						$record['kegiatan_tugas_jabatan'] = "$tugas[name] ($tugas[kode_rekening])";
					} else{
						$tugas_sql=mysqli_query($koneksi,"SELECT * FROM $database_skp.ref_js_uraian_tugas
									LEFT JOIN $database_skp.ref_satuan ON $database_skp.ref_satuan.satuan_id=$database_skp.ref_js_uraian_tugas.satuan_id 
									WHERE uraian_id='$r[tugas_jab]'");
						$tugas=mysqli_fetch_array($tugas_sql);
						$record['kegiatan_tugas_jabatan'] = "$tugas[uraian]";
					}
				}
				
				if ($l['jabatan_id']=='2'){
					$tugas_sql=mysqli_query($koneksi,"SELECT * FROM $database_skp.ref_jft_uraian_tugas 
								LEFT JOIN $database_skp.ref_satuan ON $database_skp.ref_satuan.satuan_id=$database_skp.ref_jft_uraian_tugas.satuan_id 
								WHERE uraian_id='$r[tugas_jab]'");
					$tugas=mysqli_fetch_array($tugas_sql);
					$record['kegiatan_tugas_jabatan'] = "$tugas[uraian] (AK:$tugas[angka_kredit])";
				}
				
				if ($l['jabatan_id']=='3'){
					$tugas_sql=mysqli_query($koneksi,"SELECT * FROM $database_skp.ref_jfu_uraian_tugas 
								LEFT JOIN $database_skp.ref_satuan ON $database_skp.ref_satuan.satuan_id=$database_skp.ref_jfu_uraian_tugas.satuan_id 
								WHERE uraian_id='$r[tugas_jab]'");
					$tugas=mysqli_fetch_array($tugas_sql);
					$record['kegiatan_tugas_jabatan'] = "$tugas[uraian]";
				}
				
				//-------------target----------------------------------------------------------
				$ak_bulan_utama= $tugas['angka_kredit']*$r[$bulan];
				$record['ak'] = $ak_bulan_utama;
				
				$target = [];
				if ($r['kegiatan_id']=='1' OR $r['kegiatan_id']=='2'){
					$target['kuantitas'] = "$r[kuantitas] $tugas[satuan]";
					$wp_bulan_utama=0;
				} else {
					$target['kuantitas'] = "$r[$bulan] $tugas[satuan]";
					$wp_bulan_utama= $r['wp']*$r[$bulan];
				}

				$target['kualitas'] = "$r[kualitas] %";
				
				if ($r['kegiatan_id']=='1' OR $r['kegiatan_id']=='2'){
					$biaya=number_format($r[$bulan]);
				} else {
					$biaya=number_format($r['biaya']);
				}
				$target['biaya'] = $biaya;

				$record['target'] = $target;
				
				//-------------realsisasi----------------------------------------------------------
				$tampil1=mysqli_query($koneksi,"SELECT $database_skp.rwyt_realisasi_kuantitas.* FROM $database_skp.rwyt_realisasi_kuantitas 
							LEFT JOIN $database_skp.rwyt_target_kerja_tahunan ON $database_skp.rwyt_target_kerja_tahunan.target_id=$database_skp.rwyt_realisasi_kuantitas.target_id 
							WHERE $database_skp.rwyt_realisasi_kuantitas.pegawai_id='$r[pegawai_id]' 
							AND $database_skp.rwyt_realisasi_kuantitas.breakdown_id='$r[breakdown_id]' 
							AND $database_skp.rwyt_realisasi_kuantitas.informasi_id='$r[informasi_id]' 
							AND unsur_id=1 ORDER BY $database_skp.rwyt_realisasi_kuantitas.target_id");
				$s=mysqli_fetch_array($tampil1);
				
				$realisasi = [];
				if ($r['kegiatan_id']=='1' OR $r['kegiatan_id']=='2'){
					if ($s[$bulan]==''){
						$realisasi['kuantitas'] = "";
					} else {
						$realisasi['kuantitas'] = "$s[$bulan] $tugas[satuan]";
					}
				} else {
					if ($s[$bulan]==''){
						$realisasi['kuantitas'] = "";
					} else {
						$realisasi['kuantitas'] = "$s[$bulan] $tugas[satuan]";
					}
				}
				
				$tampil2=mysqli_query($koneksi,"SELECT $database_skp.rwyt_realisasi_kualitas.* FROM $database_skp.rwyt_realisasi_kualitas 
							WHERE $database_skp.rwyt_realisasi_kualitas.pegawai_id='$r[pegawai_id]' 
							AND $database_skp.rwyt_realisasi_kualitas.breakdown_id='$r[breakdown_id]' 
							AND $database_skp.rwyt_realisasi_kualitas.informasi_id='$r[informasi_id]' 
							ORDER BY $database_skp.rwyt_realisasi_kualitas.target_id");
				$t=mysqli_fetch_array($tampil2);
				if ($t[$bulan]==''){
					$realisasi['kualitas'] = "";
				} else {
					$realisasi['kualitas'] = "$t[$bulan] %";
				}
				
				$tampil3=mysqli_query($koneksi,"SELECT $database_skp.rwyt_realisasi_biaya.* FROM $database_skp.rwyt_realisasi_biaya 
							WHERE $database_skp.rwyt_realisasi_biaya.pegawai_id='$r[pegawai_id]' 
							AND $database_skp.rwyt_realisasi_biaya.breakdown_id='$r[breakdown_id]' 
							AND $database_skp.rwyt_realisasi_biaya.informasi_id='$r[informasi_id]' 
							ORDER BY $database_skp.rwyt_realisasi_biaya.target_id");
				$u=mysqli_fetch_array($tampil3);
				if ($u[$bulan]==''){
					$realisasi['biaya'] = "";
				} else {
					$biaya=number_format($u[$bulan]);
					$realisasi['biaya'] = $biaya;
				}
				
				$tampil4=mysqli_query($koneksi,"SELECT $database_skp.rwyt_realisasi_file.* FROM $database_skp.rwyt_realisasi_file 
							WHERE $database_skp.rwyt_realisasi_file.pegawai_id='$r[pegawai_id]' 
							AND $database_skp.rwyt_realisasi_file.breakdown_id='$r[breakdown_id]' 
							AND $database_skp.rwyt_realisasi_file.informasi_id='$r[informasi_id]' 
							ORDER BY $database_skp.rwyt_realisasi_file.target_id");
				$v=mysqli_fetch_array($tampil4);
				
				if ($v[$bulan]==''){
					$realisasi['jurnal'] = "";
				} else {
					$realisasi['jurnal'] = "$url_pusdasip/$folder_files_skp/$v[$bulan]";
				}
				
				$tampil5=mysqli_query($koneksi,"SELECT $database_skp.rwyt_realisasi_link_bukti.* FROM $database_skp.rwyt_realisasi_link_bukti 
							WHERE $database_skp.rwyt_realisasi_link_bukti.pegawai_id='$r[pegawai_id]' 
							AND $database_skp.rwyt_realisasi_link_bukti.breakdown_id='$r[breakdown_id]' 
							AND $database_skp.rwyt_realisasi_link_bukti.informasi_id='$r[informasi_id]' 
							ORDER BY $database_skp.rwyt_realisasi_link_bukti.target_id");
				$v=mysqli_fetch_array($tampil5); 
				
				if ($v[$bulan]==''){
					$realisasi['link_bukti'] = "";
				} else{
					$realisasi['link_bukti'] = $v[$bulan];
				}
				
				if ($s['tgl_edit']==''){
					$realisasi['tgl_edit'] = "";
				} else {
					$tgl1=substr($s['tgl_edit'],8,2)."-".substr($s['tgl_edit'],5,2)."-".substr($s['tgl_edit'],0,4);
					$realisasi['tgl_edit'] = "$tgl1 $s[jam_edit]";
				}

				$record['realisasi'] = $realisasi;
				
				$wp_bulan_utama1=$wp_bulan_utama1+$wp_bulan_utama;
				$ak_bulan_utama1=$ak_bulan_utama1+$ak_bulan_utama;     
				
				$no++;

				array_push($kegiatan_tugas_jabatan, $record);
			}
		}
		
		$tampil11=mysqli_query($koneksi,"SELECT SUM($bulan) FROM $database_skp.rwyt_breakdown_target_kerja 
					LEFT JOIN $database_skp.rwyt_target_kerja_tahunan ON $database_skp.rwyt_target_kerja_tahunan.target_id=$database_skp.rwyt_breakdown_target_kerja.target_id 
					WHERE $database_skp.rwyt_breakdown_target_kerja.pegawai_id='$j[pegawai_id]' 
					AND $database_skp.rwyt_breakdown_target_kerja.informasi_id='$l[informasi_id]' 
					AND unsur_id=1 AND (kegiatan_id='1' OR kegiatan_id='2') ORDER BY $database_skp.rwyt_breakdown_target_kerja.target_id");
		$r11=mysqli_fetch_array($tampil11);
		$biaya1=$r11[0];
	}

?>