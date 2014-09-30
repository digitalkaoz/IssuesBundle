<?php

namespace Rs\IssuesBundle\Synchronizer;

use Rs\Issues\Tracker;
use Rs\IssuesBundle\Storage\Storage;

/**
 * Synchronizer
 * @author Robert Schönthal <robert.schoenthal@gmail.com>
 */
class JiraSynchronizer implements Synchronizer
{
    const KEY = "bcb04b7e103a0cd8b54763051cef08bc55abe029fdebae5e1d417e2ffb2a00a3";

    /**
     * @var array
     */
    private $repos = array();
    /**
     * @var Tracker
     */
    private $tracker;
    /**
     * @var Storage
     */
    private $storage;

    /**
     * @param Tracker $tracker
     * @param Storage $storage
     * @param array   $repos
     */
    public function __construct(Tracker $tracker, Storage $storage, array $repos)
    {
        $this->repos = $repos;
        $this->tracker = $tracker;
        $this->storage = $storage;
    }

    /**
     * @inheritdoc
     */
    public function synchronize($cb = null)
    {
        foreach ($this->repos as $repo) {
            $repo = $this->authorize($repo);

            $this->fetch($repo, $cb);
        }
    }

    /**
     * @param string $repo
     * @param \Closure $cb
     */
    private function fetch($repo, $cb = null)
    {
        try {
            $project = $this->tracker->getProject($repo);

            $this->storage->saveProject($project);

            foreach ($project->getIssues() as $issue) {
                $this->storage->saveIssue($issue, $project);
            }

            if (is_callable($cb)) {
                $cb(sprintf('synchronized "%s"', $repo));
            }
        } catch (\Exception $e) {
            if (is_callable($cb)) {
                $cb(sprintf('failed !%s! with %s', $repo, $e->getMessage()));
            }
        }
    }

    /**
     * @param string $repo
     * @return string
     */
    private function authorize($repo)
    {
        list($crypt, $host, $project) = explode(' ', $repo);
        list($username, $password) = explode(' ', $this->decrypt($crypt));

        $this->tracker->connect($username, $password, $host);
        $repo = $project;

        return $repo;
    }

    public static function encrypt($username, $password)
    {
        $key = pack('H*', self::KEY);
        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), MCRYPT_RAND);

        $ciphertext = $iv.mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $username.' '.$password, MCRYPT_MODE_CBC, $iv);

        return base64_encode($ciphertext);
    }

    public static function decrypt($string)
    {
        $key = pack('H*', self::KEY);
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);

        $ciphertext = base64_decode($string);
        $iv = substr($ciphertext, 0, $iv_size);
        $ciphertext = substr($ciphertext, $iv_size);

        return mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $ciphertext, MCRYPT_MODE_CBC, $iv);
    }
}
