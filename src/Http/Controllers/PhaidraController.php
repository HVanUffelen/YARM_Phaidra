<?php

namespace Yarm\Phaidra\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\ReadableParserController;
use App\Models\File;
use App\Models\Ref;
use Illuminate\Support\Facades\Storage;

class PhaidraController extends Controller

{
    static function showPhaidraStats4DLBT($Counters)
    {
        $Counters['primary'] = Ref::select('identifiers.ref_id')->leftJoin('identifiers', 'ref_id', '=', 'refs.id')
            ->where('identifiers.value', 'like', '%phaidra%')->where('primarytxt', '=', 'true')->distinct()->count();
        $Counters['secondary'] = Ref::select('identifiers.ref_id')->leftJoin('identifiers', 'ref_id', '=', 'refs.id')
            ->where('identifiers.value', 'like', '%phaidra%')->where('primarytxt', '!=', 'true')->distinct()->count();

        $Counters['primaryLang'] = Ref::select('language_target_id')->leftJoin('identifiers', 'ref_id', '=', 'refs.id')
            ->where('identifiers.value', 'like', '%phaidra%')->where('primarytxt', '=', 'true')->distinct('language_target_id')->count();
        $Counters['secondaryLang'] = Ref::select('language_target_id')->leftJoin('identifiers', 'ref_id', '=', 'refs.id')
            ->where('identifiers.value', 'like', '%phaidra%')->where('primarytxt', '!=', 'true')->distinct('language_target_id')->count();

        return $Counters;
    }

    static function citationViewDetailsPhaidra($data, $urls4info)
    {

        $data['books'] = $urls4info['PhaidraBOOK'];
        $data['tei'] = $urls4info['PhaidraTEI'];
        $data['otherObjects'] = $urls4info['PhaidraDOWNLOAD'];

        return $data;
    }

    static function loadPhaidraInfo($URLRecord, $licenses, $identifierType, $data, $i)
    {
        if ($data['identifier_type_id'] == 1) {
            $downloadLink = self::createDownloadLink($identifierType, $data['value']);
            if ($downloadLink) {
                if (strpos($downloadLink, "/download") == false) {
                    $URLRecord['PhaidraBOOK'][$i]['Type'][] = $identifierType;
                    $URLRecord['PhaidraBOOK'][$i]['Value'][] = $data['value'];
                    $URLRecord['PhaidraBOOK'][$i]['Comment'][] = $data['comment'];
                    $URLRecord['PhaidraBOOK'][$i]['License'][] = $licenses[array_search($data['license_id'], array_column($licenses, 'id'))]['name'];
                    $URLRecord['PhaidraBOOK'][$i]['Downloadlink'][] = $downloadLink;
                } else {
                    $URLRecord['PhaidraDOWNLOAD'][$i]['id'][] = $data['id'];
                    $URLRecord['PhaidraDOWNLOAD'][$i]['Type'][] = $identifierType;
                    $URLRecord['PhaidraDOWNLOAD'][$i]['Value'][] = $data['value'];
                    $URLRecord['PhaidraDOWNLOAD'][$i]['Comment'][] = $data['comment'];
                    $URLRecord['PhaidraDOWNLOAD'][$i]['License'][] = $licenses[array_search($data['license_id'], array_column($licenses, 'id'))]['name'];
                    $URLRecord['PhaidraDOWNLOAD'][$i]['Downloadlink'][] = $downloadLink;
                    $URLRecord['PhaidraDOWNLOAD'][$i]['ExtAndFileName'][] = self::getAndAddExtensionAndFileName($downloadLink);
                }
            }
        } elseif ($data['identifier_type_id'] == 2) {
            $URLRecord['PhaidraTEI'][$i]['Type'][] = $identifierType;
            $URLRecord['PhaidraTEI'][$i]['Value'][] = $data['value'];
            $URLRecord['PhaidraTEI'][$i]['Comment'][] = $data['comment'];
            $URLRecord['PhaidraTEI'][$i]['License'][] = $licenses[array_search($data['license_id'], array_column($licenses, 'id'))]['name'];
            if (isset($data['file_id']) && $data['file_id'] != 0)
                $URLRecord['PhaidraTEI'][$i]['fileId'][] = $data['file_id'];

        }
        return $URLRecord;
    }

    static function buildLinksPhaidra($data, $URLRecord, $id)
    {
        $data['PhaidraBOOK'] = '';
        $data['PhaidraDOWNLOAD'] = '';
        $data['PhaidraTEI'] = '';

        if (isset($URLRecord['PhaidraBOOK'])) {
            foreach ($URLRecord['PhaidraBOOK'] as $phaidra) {
                $data['PhaidraBOOK'] = "<li><a href=\"" . $phaidra['Downloadlink'][0] . "\" target = \"_blank\"><span class='font-weight-bold'>View with PHAIDRA book viewer: </span>" . $phaidra['Comment'][0] . "</a> [" . $phaidra['License'][0] . "]</li>";
            }
        }

        if (isset($URLRecord['PhaidraDOWNLOAD'])) {
            $data['PhaidraDOWNLOAD'] = self::makeLinksForPhaidraDownload($URLRecord, $id);
        }

        if (isset($URLRecord['PhaidraTEI'])) {
            $data['PhaidraTEI'] = self::makeLinksForPhaidraTEI($URLRecord, $id);
        }

        return $data;
    }

    private static function makeLinksForPhaidraDownload($URLRecord, $id)
    {
        $PhaidraDownload = '';

        foreach ($URLRecord['PhaidraDOWNLOAD'] as $phaidra) {
            $typeOfFile = DownloadController::checkURLIsVAlid($phaidra['Downloadlink'][0]);
            $phaidraDownloadToBookshelfYES = "<li><a href=" . $phaidra['Downloadlink'][0] . "><i data-toggle='tooltip' title='Download' class=\"fa fa-download\" style=\"color:steelblue\"></i></a> <a id='add-to-bookshelf' data-refid =" . $id . " data-name=" . "'" . $phaidra['Comment'][0] . "'" . " data-identifierid=" . $phaidra['id'][0] . " href='#'><i data-toggle='tooltip' title='Add to bookshelf' class=\"fa fa-book\" style=\"color:seagreen\"></i></a> <a href=\"" . $phaidra['Value'][0] . "\" target = \"_blank\">" . $phaidra['Comment'][0] . " (" . $typeOfFile . ")</a> (" . $phaidra['License'][0] . ")</li>";
            $phaidraDownloadToBookshelfUnchecked = "<li><a href=" . $phaidra['Downloadlink'][0] . "><i data-toggle='tooltip' title='Download' class=\"fa fa-download\" style=\"color:steelblue\"></i></a> <a id='add-to-bookshelf' data-refid =" . $id . " data-name=" . "'" . $phaidra['Comment'][0] . "'" . " data-identifierid=" . $phaidra['id'][0] . " href='#'><i data-toggle='tooltip' title='Add to bookshelf' class=\"fa fa-book\" style=\"color:orange\"></i></a> <a href=\"" . $phaidra['Value'][0] . "\" target = \"_blank\">" . $phaidra['Comment'][0] . " (" . $typeOfFile . ")</a> (" . $phaidra['License'][0] . ")</li>";
            $phaidraDownloadToBookshelfNO = "<li><a href=" . $phaidra['Downloadlink'][0] . "><i data-toggle='tooltip' title='Download' class=\"fa fa-download\" style=\"color:steelblue\"></i></a> <a id='add-to-bookshelf' data-refid =" . $id . " data-name=" . "'" . $phaidra['Comment'][0] . "'" . " data-identifierid=" . $phaidra['id'][0] . " href='#'><i data-toggle='tooltip' title='Not readable' class=\"fa fa-book\" style=\"color:red\"></i></a> <a href=\"" . $phaidra['Value'][0] . "\" target = \"_blank\">" . $phaidra['Comment'][0] . " (" . $typeOfFile . ")</a> (" . $phaidra['License'][0] . ")</li>";
            $phaidraDownloadYES = "<li><a href=" . $phaidra['Downloadlink'][0] . "><i data-toggle='tooltip' title='Download' class=\"fa fa-download\" style=\"color:steelblue\"></i></a></a> <a href=\"" . $phaidra['Value'][0] . "\" target = \"_blank\">" . $phaidra['Comment'][0] . " (" . $typeOfFile . ")</a> (" . $phaidra['License'][0] . ")</li>";

            if (Auth()->user()) {

                if ($typeOfFile !== false) {
                    if ($typeOfFile == 'Content-Type: application/pdf') {
                        try {
                            if (auth()->user()->options()->first()->check_files == 'false') {
                                $PhaidraDownload .= $phaidraDownloadToBookshelfUnchecked;
                            } else {
                                if (ReadableParserController::checkIfPdfIsReadable($phaidra['Downloadlink'][0])) {
                                    $PhaidraDownload .= $phaidraDownloadToBookshelfYES;
                                } else {
                                    $PhaidraDownload .= $phaidraDownloadToBookshelfNO;
                                }
                            }
                        } catch (\Throwable $e) {
                            $PhaidraDownload .= $phaidraDownloadToBookshelfNO;
                        }
                    } else {
                        $PhaidraDownload .= $phaidraDownloadYES;
                    }
                }
            } else {
                //even guests can download files from phaidra
                if ($typeOfFile !== false) {
                    $PhaidraDownload .= $phaidraDownloadYES;
                }
            }
        }
        return $PhaidraDownload;
    }

    private static function makeLinksForPhaidraTEI($URLRecord, $id)
    {
        $PhaidraTEI = '';

        foreach ($URLRecord['PhaidraTEI'] as $phaidra) {

            $PhaidraTEIInfo = '<span class="font-weight-bold"> ' . $phaidra['Comment'][0] . ' </span> [' . $phaidra['License'][0] . ']';

            if (Auth()->user()) {

                if ($phaidra['Type'][0] == 'PID-TEI XML') {

                    $PhaidraTEI .= '<li>';
                    try {
                        $fileObject = File::where('id', '=', $phaidra['fileId'][0])->first();
                        //Check if file exists in storage DLBTUploads
                        //Todo use directory unzipped!!
                        //Todo Check if TEI
                        if (Storage::exists('YARMDBUploads/' . $fileObject['name'])) {
                            if (strpos($fileObject['name'], '.zip') === true) {
                                $zippedFile = Storage::get('YARMDBUploads/' . $fileObject['name']);
                                $zip = new ZipArchive;
                                if ($zip->open($zippedFile) === TRUE) {
                                    $zip->extractTo('YARMDBUploads/unzipped/');
                                    $zip->close();
                                }
                            }
                            $downloadLink = self::createDownloadLink('PID', $phaidra['Value'][0]);
                            $PhaidraTEIToBookshelf = " <a href=" . $downloadLink . "><i data-toggle='tooltip' title='Download' class=\"fa fa-download\" style=\"color:steelblue\"></i></a><a id = 'add-to-bookshelf' data-refid = " . $id . " data-name = " . "'" . $fileObject['name'] . "'" . " data-id = " . $phaidra['fileId'][0] . " href='#'><i class=\"fa fa-book\" style=\"color:seagreen\"></i></a>";
                            $PhaidraTEI .= $PhaidraTEIToBookshelf . $PhaidraTEIInfo;
                        } else {
                            $PhaidraTEI .= "<b>File not found!</b>";
                        }
                    } catch (\Throwable $e) {
                        $PhaidraTEI .= "<b>File not found!</b>";
                    }
                }
            } else {
                $downloadLink = self::createDownloadLink('PID', $phaidra['Value'][0]);
                $PhaidraTEITDownloadOnly = " <a href=" . $downloadLink . "><i data-toggle='tooltip' title='Download' class=\"fa fa-download\" style=\"color:steelblue\"></i></a>";
                $PhaidraTEI .= $PhaidraTEITDownloadOnly . $PhaidraTEIInfo;
            }
            $PhaidraTEI .= '</li>';
        }
        return $PhaidraTEI;
    }

    /**
     * @param $type
     * @param $value
     * @return bool|string
     */
    public static function createDownloadLink($type, $value)
    {
        $identifierToBeSelectedToEnableShowAsPhaidra = "PID";

        if ($type == $identifierToBeSelectedToEnableShowAsPhaidra) {
            $downloadLink = self::tryToConvertToDownloadLink($value);
            if ($downloadLink !== false)
                return $downloadLink;
            else {
                return false;
            }
        }
    }

    /**
     * @param $urlSrc
     * @return bool|string
     */
    public static function tryToConvertToDownloadLink($urlSrc)
    {
        $id = self::getPIDIdOutOfURL($urlSrc);
        $apiUrl = config('yarm.phaidra_api');
        $result = json_decode(file_get_contents($apiUrl . 'o:' . $id . '/cmodel'), true);
        if (is_array($result)) {
            if ($result['cmodel'] == 'Book')
                $url = $urlSrc;
            else
                $url = $apiUrl . 'o:' . $id . '/download';
            return $url;
        } else
            return false;
    }

    /**
     * @param $urlSrc
     * @return bool|string
     */
    private static function CreateStandardPhaidraUrl($urlSrc)
    {
        $phaidraServers = array("phaidra-sandbox" => "PS", "test" => "PT", "phaidra" => "P");;

        $id = self::getPIDIdOutOfURL($urlSrc);
        $phaidraServer = self::getPhaidraServerOutOfURL($urlSrc);
        if ($id === false || $phaidraServer === false) {
            return false;
        }

        $key = array_search($phaidraServer, $phaidraServers);
        return "https://fedora." . $key . ".univie.ac.at/fedora/objects/o:" . $id;
    }

    /**
     * @param $url
     * @return bool|mixed
     */
    public static function getPhaidraServerOutOfURL($url)
    {
        //voorbeeld https://fedora.phaidra-sandbox.univie.ac.at/fedora/objects/o:191162/methods/bdef:Content/get
        $phaidraServers = array("phaidra-sandbox" => "PS", "test" => "PT", "phaidra" => "P");;

        foreach ($phaidraServers as $s => $value) {
            if (preg_match('/' . $s . '/', $url, $matches, PREG_OFFSET_CAPTURE)) {
                return $value;
            }
        }

        return false;
    }

    /**
     * @param $downloadLink
     * @return false|string
     */
    public static function getAndAddExtensionAndFileName($downloadLink)
    {
        $id = self::getPIDIdOutOfURL($downloadLink);
        $apiUrl = config('yarm.phaidra_api');
        $result = json_decode(file_get_contents($apiUrl . 'o:' . $id . '/cmodel'),true);
        return $result['cmodel'];
    }

    /**
     * @param $url
     * @return bool|false|string
     */
    public static function getPIDIdOutOfURL($url)
    {
        //voorbeeld https://fedora.phaidra-sandbox.univie.ac.at/fedora/objects/o:191162/methods/bdef:Content/get
        //ww4dlb don't look only for 6 digits, there are objects with 1 digit

        if (!preg_match('/o:[0-9]+/', $url, $matches, PREG_OFFSET_CAPTURE)) {
            return false;
        }

        return substr($matches[0][0], 2);
    }

}
