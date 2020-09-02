# query
light ORM based by [flatphp/lightdb](https://github.com/flatphp/lightdb)

# usage
```php
use \Flatphp\Query\QueryResolver;
use \Flatphp\Query\Model;
use \Flatphp\Query\DbQuery;
use \Lightdb\DB;

$conf = array(
	'dsn' => 'mysql:host=localhost;dbname=sc_web',
	'username' => 'name',
	'password' => '123456',
	'options' => [\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'],
);
DB::init($conf);


/**
 * @property string $ch_code
 * @property string $ch_name
 */
class ChannelModel extends Model
{
}

/**
 * @method static ChannelQuery channel()
 */
class ChannelQuery extends DbQuery
{
	protected $table = 'cat_channel';
	protected $model = 'ChannelModel';

    public function whereChCode($value)
    {
        $this->query->where('ch_code=?', $value);
        return $this;
    }
}

/**
 * @method static ChannelQuery channel()
 */
class Query extends QueryResolver
{
    protected static $namespace = __NAMESPACE__;
}

$channel = Query::channel()->whereChCode('test')->getOne();
$channels = Query::channel()->getAll();
Query::channel()->insert($channel);
Query::channel()->update($channel);
Query::channel()->whereChCode('hello')->delete();

// debug mode
$sql = Query::channel()->whereChCode('test')->preview()->getOne();
print_r($sql);

$sql = Query::channel()->whereChCode('test')->preview()->update($channel);
print_r($sql);
```