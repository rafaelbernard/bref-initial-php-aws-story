import { CfnOutput, Duration, Stack, StackProps } from 'aws-cdk-lib';
import { Construct } from 'constructs';
import * as sqs from 'aws-cdk-lib/aws-sqs';
import { ServerlessLaravel } from 'cdk-serverless-lamp';
import * as path from 'path';
import * as lambda from "aws-cdk-lib/aws-lambda";
import { Code, LayerVersion, Runtime, Function as CdkFunction } from 'aws-cdk-lib/aws-lambda';

export class CdkStack extends Stack {
  constructor(scope: Construct, id: string, props?: StackProps) {
    super(scope, id, props);

    // The code that defines your stack goes here

    // example resource
    const queue = new sqs.Queue(this, 'CdkQueue', {
      visibilityTimeout: Duration.seconds(300)
    });

    // // Get Bref layer ARN from https://runtimes.bref.sh/
    // // At this page, select correct Region and PHP version
    const phpRuntimeLayer = LayerVersion.fromLayerVersionArn(this, 'php-81-fpm', 'arn:aws:lambda:us-east-1:209497400698:layer:php-81-fpm:19');
    //
    // const myFunction = new Function(this, 'myfunction', {
    //   runtime: Runtime.PROVIDED, // for custom runtime
    //   code: Code.fromAsset('../laravel58-cdk'),
    //   handler: 'public/index.php',
    //   layers: [phpRuntimeLayer],
    // });

    const myFunction = new CdkFunction(this, 'php-lambda', {
    //const myFunction = new lambda.Function(this, 'php-lambda', {
        runtime: Runtime.PROVIDED, // for custom runtime
        code: Code.fromAsset('../php'),
        handler: 'serverless/index.php',
        layers: [phpRuntimeLayer],
    });

    const laravelBut = new ServerlessLaravel(this, 'serverless-laravel', {
      brefLayerVersion: 'arn:aws:lambda:us-east-1:209497400698:layer:php-81-fpm:19',
      laravelPath: path.join(__dirname, '../php'),
      handler: myFunction
    })

    new CfnOutput(this, 'sqs', {
      value: queue.queueName
    });
  }
}
