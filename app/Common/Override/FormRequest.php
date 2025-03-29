<?php

namespace App\Common\Override;

use App\Common\Common\Arr;
use App\Common\Constants\BusinessErrorCode;
use App\Common\Constants\InternalErrorCode;
use App\Common\Exception\InternalException;
use App\Common\Override\Validation\Validator;
use Exception;
use Hyperf\Context\Context;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\ValidatorFactory;

/**
 * 扩展表单请求类
 */
class FormRequest extends \Hyperf\Validation\Request\FormRequest
{
    protected  array $additionalData = [];

    /**
     * 获取对象实例
     *
     * @return static|null
     */
    public static function get(): ?static
    {
        // return Context::get(self::class);
        return container(static::class);
    }


    /**
     * 设置附加数据
     *  -附加数据可以存放从数据库查询或复杂计算的结果以供后续的字段验证共用，避免重复查询/计算，
     *  如：验证数据所属组字段与用户所属组是否一致时查询了用户信息，并设置附加数据，
     *      后面又要验证数据的类型和用户的等级是否匹配时就不需要再查一次了。
     * @param [type] $key
     * @param [type] $value
     * @return void
     */
    public function setAdditional($key, $value)
    {
        $this->additionalData[$key] = $value;
        Context::set($this->additionalKey($key), $value);
    }

    private function additionalKey($key)
    {
        return static::class . '.additional.' . $key;
    }


    public function getAdditional($key, $default = null)
    {
        return Context::getOrSet($this->additionalKey($key), $default);
    }


    protected function setValidatorMessages(array $messages)
    {
        return $this->getValidatorInstance()->setCustomMessages($messages);
    }

    /**
     * 设置请求数据
     *
     * @param array $data
     * @return mixed
     */
    protected function setData(array $data, string $attribute = '', $value = null, $merge = false): mixed
    {
        if ($attribute)
            Arr::set($data, $attribute, $value);
        if ($merge) $data = array_merge($this->all(), $data);
        return Context::set($this->contextkeys['parsedData'], $data);
    }

    /**
     * 设置请求参数
     *
     * @param array $data
     * @return mixed
     */
    public function set(string $attribute = '', $value = null): mixed
    {
        return $this->setData($this->all(), $attribute, $value);
    }

    /**
     * 设置请求参数默认值
     *
     * @param array $data
     * @return mixed
     */
    public function setDefault(string $attribute, $value = ''): mixed
    {
        return $this->set($attribute,$this->input($attribute,'')===''?$value:$this->input($attribute));
    }


    protected function validator(ValidatorFactory $factory){
        $factory->resolver(function(...$args){
            return new Validator(...$args);
        });
        return $factory->make(
            $this->validationData(),
            $this->getRules(),
            $this->messages(),
            $this->attributes()
        );
    }


    /**
     * 自动处理验证错误信息
     *
     * @return array
     */
    public function messages(): array
    {
        $rules = $this->rules();
        $messages = [];
        try {
            foreach ($rules as  $k => $rule) {
                if (!is_array($rule)) {
                    $rule = explode('|', $rule);
                }
                foreach ($rule as $r) {
                    if ($r == 'required')
                        $messages[$k . '.' . $r] = '缺少参数：:attribute';
                    else {
                        if (!is_array($r))
                            $r = explode(':', $r);
                        $messages[$k . '.' . $r[0]] = ':attribute错误';
                    }
                }
            }
        } catch (Exception $e) {
            throw new InternalException(BusinessErrorCode::CUSTOM_ERROR, '验证规则格式错误');
        }

        return $messages;
    }


    public function authorize(): bool
    {
        return true;
    }


    public function validateResolved(){
        parent::validateResolved();
    }


    /**
     * 设置或获取bail
     *
     * @param boolean|null $value
     * @return boolean
     */
    public function bail(bool $value = null): bool
    {
        return $this->getValidatorInstance()->bail($value);
    }
}
