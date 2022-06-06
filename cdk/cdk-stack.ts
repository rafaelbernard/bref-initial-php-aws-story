import { Stack, StackProps } from 'aws-cdk-lib';
import { Construct } from 'constructs';
import { ServerlessLaravel } from 'cdk-serverless-lamp';
import * as path from 'path';

export class CdkStack extends Stack {
  constructor(scope: Construct, id: string, props?: StackProps) {
    super(scope, id, props);
    // Get Bref layer ARN from https://runtimes.bref.sh/
    const laravelOnly = new ServerlessLaravel(this, 'serverless-laravel', {
      brefLayerVersion: 'arn:aws:lambda:us-east-1:209497400698:layer:php-81-fpm:19',
      laravelPath: path.join(__dirname, '../php'),
    });
  }
}
