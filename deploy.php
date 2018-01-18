<?php

namespace Deployer;

require 'recipe/common.php';

// Project name
set( 'application', 'cfpnew.phpdd.org' );

// Project repository
set( 'repository', 'https://github.com/php-usergroup-dresden/opencfp.git' );

// [Optional] Allocate tty for git clone. Default value is false.
set( 'git_tty', true );

// Shared files/dirs between deploys 
set( 'shared_files', ['config/production.yml'] );
set( 'shared_dirs', ['cache', 'web/uploads', 'log'] );

// Writable dirs by web server 
set( 'writable_dirs', ['/var/www/{{application}}/shared/web/uploads', '/var/www/{{application}}/shared/cache', '/var/www/{{application}}/shared/log'] );
set( 'writable_mode', 'chmod' );
set( 'writable_use_sudo', true );
set( 'writable_chmod_mode', '0777' );
set( 'writable_chmod_recursive', true );

set( 'allow_anonymous_stats', false );

// Hosts

host( 'phpdd.org' )
	->user( 'deploy' )
	->multiplexing( true )
	->forwardAgent( false )
	->addSshOption( 'UserKnownHostsFile', '/dev/null' )
	->addSshOption( 'StrictHostKeyChecking', 'no' )
	->set( 'deploy_path', '/var/www/{{application}}' );

// Tasks

desc( 'Clear caches' );
task(
	'cache:clear',
	function ()
	{
		within(
			'{{release_path}}',
			function ()
			{
				run( 'CFP_ENV=production bin/console cache:clear' );
				run( 'CFP_ENV=production bin/console cache:warmup' );
			}
		);
		run( 'sudo mkdir -pm 0777 /var/www/{{application}}/shared/cache/production/sessions' );
		run( 'sudo chown -R deploy:www-data /var/www/{{application}}/shared/cache' );
	}
);

desc( 'Deploy your project' );
task(
	'deploy',
	[
		'deploy:info',
		'deploy:prepare',
		'deploy:lock',
		'deploy:release',
		'deploy:update_code',
		'deploy:shared',
		'deploy:writable',
		'deploy:vendors',
		'deploy:clear_paths',
		'cache:clear',
		'deploy:symlink',
		'deploy:unlock',
		'cleanup',
		'success',
	]
);

// [Optional] If deploy fails automatically unlock.
after( 'deploy:failed', 'deploy:unlock' );
