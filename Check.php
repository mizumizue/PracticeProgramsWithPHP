<?php
/**
 * 自作チェック関数クラス
 */
class Check
{
        /**
     * NGワードが含まれるワードなのかチェックする
     * @param array $ngwords NGワードの配列
     * @param string  $checkWord チェックする文字列
     * @param string $regexOption 正規表現のオプション 省略可
     * @return bool
     */
    public function isNgWord(array $ngwords, $checkWord, $regexOption = ''):bool
    {
        foreach ($this->NG_WORD_LIST as $ngWord) {
            $pattern = '/' . $ngWord . '/' . $regexOption; // 正規表現パターン作成
            if (preg_match($pattern, $checkWord)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 半角英数字チェック(英は大文字のみ)
     * @param string $checkWord チェック対象の文字列
     * @return bool true | false
     */
    public function isAlphanumeric($checkWord):bool
    {
        return preg_match('/^[0-9a-zA-Z]+$/', $checkWord);
    }

    /**
     * 半角英数字チェック(英は大文字のみ)
     * @param string $checkWord チェック対象の文字列
     * @return bool
     */
    public function isAlphanumericUppercase($checkWord):bool
    {
        return preg_match('/^[0-9A-Z]+$/', $checkWord);
    }
    
    /**
     * 半角英数字チェック(英は小文字のみ)
     * @param string $checkWord チェック対象の文字列
     * @return bool
     */
    public function isAlphanumericLowercase($checkWord):bool
    {
        return preg_match('/^[0-9a-z]+$/', $checkWord);
    }

    /**
     * 文字列の長さチェック
     * @param string $checkWord チェック対象の文字列
     * @param int $maxLength 最大文字数
     * @return bool
     */
    public function isWithinMaxLength($checkWord, $maxLength):bool
    {
        $wordLength = strlen($checkWord);
        return ($wordLength > $maxLength);
    }
}
