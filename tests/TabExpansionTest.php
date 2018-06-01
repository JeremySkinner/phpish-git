<?php

use PHPUnit\Framework\TestCase;


class TabExpansionTest extends TestCase {

  public static $status = [];

  public static function setUpBeforeClass() {
    self::$status = Git::status(new GitSettings());
  }

  public static function tearDownAfterClass() {
    removeAllAliases();
  }

  public function testCompletesWithoutSubCommands() {
    $result = expand('git whatever');
    $this->assertEmpty($result->expansions);
  }

  public function testBisect() {
    expand('git bisect ')
      ->contains('', FALSE)
      ->contains('start')
      ->contains('run');

    expand('git bisect s')
      ->contains('start')
      ->contains('skip')
      ->contains('run', FALSE);
  }

  public function testRemote() {
    expand('git remote ')
      ->contains('', FALSE)
      ->contains('add')
      ->contains('set-branches')
      ->contains('get-url')
      ->contains('update');

    expand('git remote s')
      ->contains('set-branches')
      ->contains('set-head')
      ->contains('set-url')
      ->contains('update', FALSE);

  }

  public function testExpandsRemotes() {
    expand('git push ')
      ->contains('origin')
      ->contains('', FALSE);
  }

  public function testAllBranches() {
    expand('git push origin ')
      ->contains('master')
      ->contains('origin/master')
      ->contains('origin/HEAD')
      ->contains('', FALSE);
  }

  public function testAllColonBranches() {
    expand('git push origin :')
      ->contains(':master')
      ->contains('', FALSE);
  }

  public function testMatchingRemotes() {
    expand('git push o')
      ->single('origin');
  }

  public function testMatchingBranch() {
    expand('git push origin ma')
      ->single('master');
  }

  public function testMatchingRemoteSlashBranch() {
    expand('git push origin origin/ma')
      ->single('origin/master');
  }

  public function testCompletesMatchingColonBranches() {
    expand('git push origin :ma')
      ->single(':master');
  }

  public function testMatchingRefColonBranches() {
    expand('git push origin HEAD:ma')
      ->single('HEAD:master');
  }

  public function testMatchingPlusRefColonBranches() {
    expand('git push origin +HEAD:ma')
      ->single('+HEAD:master');
  }

  public function testMatchesRemoteWithPrecedingParameter() {
    expand('git push --follow-tags  -u   or')
      ->single('origin');
  }

  public function testCompltesAllbranchesWithPrecedingParameter() {
    expand('git push --follow-tags  -u   origin ')
      ->contains('master')
      ->contains('origin/master')
      ->contains('origin/HEAD');
  }

  public function testMatchesBranchWithPrecedingParameter() {
    expand('git push --follow-tags  -u   origin ma')
      ->single('master');
  }

  public function testMatchesBranchWithIntermixedParameters() {
    expand('git push -u origin --follow-tags ma')
      ->single('master');

    expand('git push  -u  origin  --follow-tags   ma')
      ->single('master');
  }

  public function testCompltesMatchingRefColonBranchWithIntermixedParameters() {
    expand('git push -u origin --follow-tags HEAD:ma')
      ->single('HEAD:master');

    expand('git push  -u  origin  --follow-tags   +HEAD:ma')
      ->single('+HEAD:master');
  }

  public function testMatchesMultiplePushRefSpecsWithIntermixedParameters() {
    expand('git push -u origin --follow-tags one :two three:four  ma')
      ->single('master');

    expand('git push -u origin --follow-tags one :two three:four  --crazy-param ma')
      ->single('master');

    expand('git push -u origin --follow-tags one :two three:four  HEAD:ma')
      ->single('HEAD:master');

    expand('git push -u origin --follow-tags one :two three:four  --crazy-param HEAD:ma')
      ->single('HEAD:master');

    expand('git push -u origin --follow-tags one :two three:four  +ma')
      ->single('+master');
    expand('git push -u origin --follow-tags one :two three:four  --crazy-param +ma')
      ->single('+master');
    expand('git push  -u  origin  --follow-tags one :two three:four  +HEAD:ma')
      ->single('+HEAD:master');
    expand('git push  -u  origin  --follow-tags  one :two three:four  --crazy-param  +HEAD:ma')
      ->single('+HEAD:master');
  }

  public function testReturnsEmptyResultForMissingRemote() {
    expand('git push zy')
      ->empty();
  }

  public function testReturnsEmptyResultForMissingBranch() {
    expand('git push origin zy')
      ->empty();
  }
  public function testReturnsEmptyForMissingRemoteBranch() {
    expand('git fetch origin/zy')
      ->empty();
  }

  public function testCompletesRemoteBranchNamesWithDashes() {
    $branch_name = 'test-branch--with-dashes';
    try {

      Git::exec("branch $branch_name", $rtn);
      expand('git push origin test-')
        ->single($branch_name);

      expand('git push  --follow-tags  -u   origin ')
        ->contains($branch_name);
    }
    finally {
      Git::exec("branch -D $branch_name", $rtn);
    }

  }

  public function testCommandCompletionIncludesAliases() {
    $alias = 'test-' . rand();
    addAlias($alias, 'help');

    $this->assertEquals(1, count(expand("git $alias")->expansions));
  }

  public function testCompletesWhenThereIsOneAliasOfAGivenName() {
    $alias = "test-" . rand();
    addAlias($alias, 'checkout');
    expand("git $alias ma")
      ->single('master');
  }

  public function testCompletesWhenMultipleAliasesOfSameName() {
    $alias1 = "test-" . rand();
    $alias2 = "test-" . rand();

    addAlias($alias1, 'checkout');
    addAlias($alias2, 'checkout');

    expand("git $alias1 ma")
      ->single('master');
  }

  public function testGitk() {
    expand('gitk ')
      ->contains('master');
  }

  public function testExpandsBaseCommands() {
    expand('git ')
      ->contains('checkout');

    expand('git check')
      ->single('checkout');
  }

  public function testBranch() {
    expand('git branch -d ')
      ->contains('master');

    expand('git branch -d ma')
      ->single('master');

    expand('git branch foo ')
      ->contains('master');

    expand('git branch foo ma')
      ->contains('master');
  }


  public function testHelp() {
    expand('git help ')
      ->contains('checkout');
  }

  public function testReset() {
    try {
      $myfile = fopen("foo.txt", "w");
      fwrite($myfile, "foo");

      expand('git RESET HEAD ')
        ->contains('foo.txt');

      expand('git RESET HEAD -- ')
        ->contains('foo.txt');
    }
    finally {
      if($myfile) {
        fclose($myfile);
        unlink('foo.txt');
      }
    }
  }

  public function handlesCheckout() {
    expand('git checkout ')
      ->contains('master')
      ->contains('origin/master')
      ->contains('origin/HEAD')
      ->contains('', FALSE);

    //@todo should test tags in here too
  }

//  public function testCheckoutDashDash() {
//    expand('git checkout -- ')
//  }

  public function testRm() {
    expand('git rm ')
      ->contains('autoload.php');
  }

  public function testDiff() {
    //@todo
    expand('git diff ');
    expand('git difftool ');
  }

  public function testMerge() {
    //@todo
    expand('git merge ');
    expand('git mergetool ');
  }

  public function testWorktree() {
    //@todo
  }

  public function testCommandRef() {
    expand('git cherry-pick ')
      ->contains('master');

    //@todo tags
  }

  public function testCommandParams() {
    expand('git init --')
      ->contains('--quiet')
      ->contains('--bare')
      ->contains('--shared=');
  }

  public function testCommandParamValues() {
    expand('git blame --encoding=')
      ->contains('--encoding=utf-8')
      ->contains('--encoding=none');
  }

  public function testShortParams() {
    expand('git init -')
      ->contains('-q');
  }
}

