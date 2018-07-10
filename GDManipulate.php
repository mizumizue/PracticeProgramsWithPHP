<?php

/**
 * GD画像ライブラリの関数ラッパー
 */
class GDManipulate
{

    /**
     * 画像をリサイズしてリソースを返す
     * @param resource  $baseImage      リサイズ元の画像リソース
     * @param int       $baseW          リサイズ元の画像サイズ(横)
     * @param int       $baseH          リサイズ元の画像サイズ(縦)
     * @param int       $resizedW       リサイズ後の画像サイズ(横)
     * @param int       $resizedH       リサイズ後の画像サイズ(縦)
     * @return resource $resizedImage   リサイズ後の画像リソース
     */
    public static function resizeImage($baseImage, $baseW, $baseH, $resizedW, $resizedH)
    {
        $resizedImage = imagecreatetruecolor($resizedW, $resizedH);
        imagealphablending($resizedImage, false);   // 透過背景が黒くなるので、その対策としての設定
        imagesavealpha($resizedImage, true);        // 透過背景が黒くなるので、その対策としての設定
        // リサイズ,土台に縮小した画像をコピーする
        $resizedPosition = ['afterX' => 0, 'afterY' => 0, 'beforeX' => 0, 'beforeY' => 0];
        // imagecopyではなく、imagecopyresampledすることで、小さくしても画像が荒くなりにくい
        $isSuccessResized = imagecopyresampled(
            $resizedImage,
            $baseImage,
            $resizedPosition['afterX'],
            $resizedPosition['afterY'],
            $resizedPosition['beforeX'],
            $resizedPosition['beforeY'],
            $resizedW,
            $resizedH,
            $baseW,
            $baseH
        );
        if (!$isSuccessResized) {
            return false;
        }
        return $resizedImage;
    }

    /**
     * 画像を回転してリソースを返す
     * @param resource  $baseImage      回転する画像のリソース
     * @param float     $angle          回転角度。正方向の数で反時計周りに回転。
     * @param array     $rgba           rgba値の配列
     * @return resource $rotatedImage   回転後の画像リソース
     */
    public static function rotateImage($baseImage, $angle, $rgba)
    {
        // 透過色を作成。回転した時に、はみ出た部分の色を透過色に指定しないと色がついてしまう
        $colorAlpha = imagecolorallocatealpha(
            $baseImage,
            $rgba['red'],
            $rgba['green'],
            $rgba['blue'],
            $rgba['alpha']
        );
        $rotatedImage = imagerotate($baseImage, $angle, $colorAlpha);
        if (!$rotatedImage) {
            return false;
        }
        return $rotatedImage;
    }

    /**
     * 渡された二つの画像リソースを指定された位置に合成する(GD利用)
     * @param resource  $baseImage      貼り付け先の画像リソース
     * @param resource  $pasteImage     貼り付ける画像リソース
     * @param int       $pasteX         貼り付け先への貼り付け位置(x座標)
     * @param int       $pasteY         貼り付け先への貼り付け位置(y座標)
     * @param int       $pasteImageW    貼り付け画像width
     * @param int       $pasteImageH    貼り付け画像height
     * @return bool     true 貼り付け成功 | false 貼り付け失敗
     */
    public static function pasteImage($baseImage, $pasteImage, $pasteX, $pasteY, $pasteImageW, $pasteImageH)
    {
        $pasteCut = ['x' => 0, 'y' => 0];   // 貼り付ける画像の座標。貼り付ける画像を切り取って貼り付けたい場合に指定。
        $isSuccessPaste = imagecopy($baseImage, $pasteImage, $pasteX, $pasteY, $pasteCut['x'], $pasteCut['y'], $pasteImageW, $pasteImageH);
        if (!$isSuccessPaste) {
            return false;
        }
        return true;
    }

    /**
     * imagettftextで画像に出力する文字を縁取る(GD利用)
     * 参考サイト：http://wmh.github.io/hunbook/examples/gd-imagettftext.html
     * @param resource $image       画像のリソース
     * @param int      $fontSize    出力する文字のフォントサイズ
     * @param int      $angle       出力する文字の傾き
     * @param int      $x           出力する文字のx座標
     * @param int      $y           出力する文字のy座標
     * @param int      $fontColor   出力する文字のフォントカラー
     * @param int      $strokeColor 出力する文字を縁取るカラー
     * @param string   $fontFile    出力する文字のフォントタイプ
     * @param int      $strokepx    縁取りのpx数
     * @return array   imagettftext テキストの境界を 構成する 4 点を表す 8 個の要素を有する配列
     */
    private function imagettfstroketext($image, $fontSize, $angle, $x, $y, $fontColor, $strokeColor, $fontFile, $text, $strokepx)
    {
        for ($c1 = ($x - abs($strokepx)); $c1 <= ($x + abs($strokepx)); $c1++) {
            for ($c2 = ($y - abs($strokepx)); $c2 <= ($y + abs($strokepx)); $c2++) {
                $bg = imagettftext($image, $fontSize, $angle, $c1, $c2, $strokeColor, $fontFile, $text);
            }
        }
        return imagettftext($image, $fontSize, $angle, $x, $y, $fontColor, $fontFile, $text);
    }
}
