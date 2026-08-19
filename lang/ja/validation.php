<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => ':Attributeを承認してください。',
    'accepted_if' => ':Otherが:valueの場合、:attributeを承認する必要があります。',
    'active_url' => ':Attributeは、有効なURLではありません。',
    'after' => ':Attributeには、:dateより後の日付を指定してください。',
    'after_or_equal' => ':Attributeには、:date以降の日付を指定してください。',
    'alpha' => ':Attributeには、アルファベッドのみ使用できます。',
    'alpha_dash' => ':Attributeには、英数字とハイフンと下線が使用できます。',
    'alpha_num' => ':Attributeには、英数字が使用できます。',
    'array' => ':Attributeには、配列を指定してください。',
    'before' => ':Attributeには、:dateより前の日付を指定してください。',
    'before_or_equal' => ':Attributeには、:date以前の日付を指定してください。',
    'between' => [
        'numeric' => ':Attributeには、:minから、:maxまでの数字を指定してください。',
        'file' => ':Attributeには、:min KBから:max KBまでのサイズのファイルを指定してください。',
        'string' => ':Attributeは、:min文字から:max文字にしてください。',
        'array' => ':Attributeの項目は、:min個から:max個にしてください。',
    ],
    'boolean' => ':Attributeには、trueかfalseを指定してください。',
    'confirmed' => ':Attributeと:attribute確認が一致しません。',
    'current_password' => 'パスワードが正しくありません。',
    'date' => ':Attributeは、正しい日付ではありません。',
    'date_equals' => ':Attributeは:dateと同じ日付を入力してください。',
    'date_format' => ':Attributeの形式が:formatと一致しません。',
    'declined' => ':Attributeを拒否する必要があります。',
    'declined_if' => ':Otherが:valueの場合、:attributeを拒否する必要があります。',
    'different' => ':Attributeと:otherには、異なるものを指定してください。',
    'digits' => ':Attributeは、:digits桁にしてください。',
    'digits_between' => ':Attributeは、:min桁から:max桁にしてください。',
    'dimensions' => ':Attributeの画像サイズが無効です。',
    'distinct' => ':Attributeの値が重複しています。',
    'email' => ':Attributeは、有効なメールアドレス形式で指定してください。',
    'ends_with' => ':Attributeの終わりは「:values」である必要があります。',
    'enum' => '選択した:attributeは無効です。',
    'exists' => '選択された:attributeは、有効ではありません。',
    'file' => ':Attributeには、ファイル形式を指定してください。',
    'filled' => ':Attributeは必須です。',
    'gt' => [
        'numeric' => ':Attributeは、:valueより大きい必要があります。',
        'file' => ':Attributeは、:value KBより大きい必要があります。',
        'string' => ':Attributeは、:value文字を超える必要があります。',
        'array' => ':Attributeの項目数は、:value個より多い必要があります。',
    ],
    'gte' => [
        'numeric' => ':Attributeは、:value以上である必要があります。',
        'file' => ':Attributeは、:value KB以上である必要があります。',
        'string' => ':Attributeは、:value文字以上である必要があります。',
        'array' => ':Attributeの項目数は、:value個以上である必要があります。',
    ],
    'image' => ':Attributeには、画像を指定してください。',
    'in' => '選択された:attributeは、有効ではありません。',
    'in_array' => ':Attributeが:otherに存在しません。',
    'integer' => ':Attributeには、整数を指定してください。',
    'ip' => ':Attributeには、有効なIPアドレスを指定してください。',
    'ipv4' => ':AttributeはIPv4アドレスを指定してください。',
    'ipv6' => ':AttributeはIPv6アドレスを指定してください。',
    'json' => ':Attributeには、有効なJSON文字列を指定してください。',
    'lt' => [
        'numeric' => ':Attributeは、:valueより小さい必要があります。',
        'file' => ':Attributeは、:value KBより小さい必要があります。',
        'string' => ':Attributeは、:value文字より小さい必要があります。',
        'array' => ':Attributeの項目数は、:value個より少ない必要があります。',
    ],
    'lte' => [
        'numeric' => ':Attributeは、:value以下である必要があります。',
        'file' => ':Attributeは、:value KB以下である必要があります。',
        'string' => ':Attributeは、:value文字以下である必要があります。',
        'array' => ':Attributeの項目数は、:value個以下である必要があります。',
    ],
    'mac_address' => ':Attributeは有効なMACアドレスである必要があります。',
    'max' => [
        'numeric' => ':Attributeは、:max以下の数値である必要があります。',
        'file' => ':Attributeは、:max KB以下のファイルである必要があります。',
        'string' => ':Attributeの文字数は、:max文字以下である必要があります。',
        'array' => ':Attributeの項目数は、:max個以下である必要があります。',
    ],
    'mimes' => ':Attributeには、以下のファイルタイプを指定してください。:values',
    'mimetypes' => ':Attributeには、以下のファイルタイプを指定してください。:values',
    'min' => [
        'numeric' => ':Attributeには、:min以上の数値を指定してください。',
        'file' => ':Attributeには、:min KB以上のファイルを指定してください。',
        'string' => ':Attributeの文字数は、:min文字以上である必要があります。',
        'array' => ':Attributeの項目数は、:min個以上にしてください。',
    ],
    'multiple_of' => ':Attributeは:valueの倍数である必要があります。',
    'not_in' => '選択された:attributeは、有効ではありません。',
    'not_regex' => ':Attributeの形式が正しくありません。',
    'numeric' => ':Attributeには、数値を指定してください。',
    'password' => 'パスワードが正しくありません。',
    'present' => ':Attributeが存在している必要があります。',
    'prohibited' => ':Attributeの入力は禁止されています。',
    'prohibited_if' => ':Otherが:valueの場合は、:Attributeの入力が禁止されています。',
    'prohibited_unless' => ':Otherが:valuesでない限り、:Attributeの入力は禁止されています。',
    'prohibits' => ':Otherが存在している場合、:Attributeの入力は禁止されています。',
    'regex' => ':Attributeには、正しい形式を指定してください。',
    'required' => ':Attributeを入力してください',
    'required_array_keys' => ':Attributeには、:valuesのエントリを含める必要があります。',
    'required_if' => ':Otherが:valueの場合、:attributeを指定してください。',
    'required_unless' => ':Otherが:values以外の場合、:attributeは必須項目です。',
    'required_with' => ':Valuesが入力されている場合、:attributeは必須項目です。',
    'required_with_all' => ':Valuesが全て指定されている場合、:attributeは必須項目です。',
    'required_without' => ':Valuesが入力されていない場合、:attributeは必須項目です。',
    'required_without_all' => ':Valuesが全て指定されていない場合、:attributeを指定してください。',
    'same' => ':Attributeと:otherが一致しません。',
    'size' => [
        'numeric' => ':Attributeには、:sizeを指定してください。',
        'file' => ':Attributeには、:size KBのファイルを指定してください。',
        'string' => ':Attributeの文字数は、:size文字にしてください。',
        'array' => ':Attributeの項目数は、:size個にしてください。',
    ],
    'starts_with' => ':Attributeは、次のいずれかで始まる必要があります。:values',
    'string' => ':Attributeには、文字列を指定してください。',
    'timezone' => ':Attributeには、有効なタイムゾーンを指定してください。',
    'unique' => '指定の:attributeは既に使用されています。',
    'uploaded' => ':Attributeのアップロードに失敗しました。',
    'url' => ':Attributeは、有効なURL形式で指定してください。',
    'uuid' => ':Attributeは、有効なUUIDである必要があります。',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */
    'custom' => [
        'name' => [
            'required' => '名前の入力は必須です',
            'string' => '文字列で入力してください',
            'max' => '名前は20文字以内で入力してください',
        ],
        'email' => [
            'required' => 'メールアドレスの入力は必須です',
            'string' => '文字列で入力してください',
            'email' => 'アドレス形式で入力してください',
            'max' => 'メールアドレスは255文字以内で入力してください。',
            'unique' => 'このメールアドレスはすでに登録されています',
        ],
        'password' => [
            'required' => 'パスワードの入力は必須です',
            'string' => '文字列で入力してください',
            'min' => 'パスワードは8文字以上で入力してください',
            'confirmed' => 'パスワードが一致していません',
        ],
        'password_confirmation' => [
            'required' => '確認用パスワードの入力は必須です',
            'string' => '文字列で入力してください',
            'min' => 'パスワードは8文字以上で入力してください',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'name' => 'お名前',
        'email' => 'メールアドレス',
        'password' => 'パスワード',
    ],

];
