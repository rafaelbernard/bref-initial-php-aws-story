import { CfnOutput, RemovalPolicy, Stack, StackProps } from 'aws-cdk-lib';
import { Construct } from 'constructs';
import { FunctionUrlAuthType, Runtime } from 'aws-cdk-lib/aws-lambda';
import { join } from 'path';
import { Bucket } from 'aws-cdk-lib/aws-s3';
import { packagePhpCode, PhpFunction } from "@bref.sh/constructs";
import { AttributeType, Table } from "aws-cdk-lib/aws-dynamodb";

export class BrefStack extends Stack {

  constructor(scope: Construct, id: string, props?: StackProps) {
    super(scope, id, props);

    const stackPrefix = 'BrefStory';

    const TableName = `${stackPrefix}-table`;

    const table = new Table(this, TableName, {
      partitionKey: { name: 'PK', type: AttributeType.STRING },
      sortKey: { name: 'SK', type: AttributeType.STRING },
      removalPolicy: RemovalPolicy.DESTROY,
      tableName: TableName,
    });

    const brefBucket = new Bucket(this, `${stackPrefix}Bucket`, {
      autoDeleteObjects: true,
      removalPolicy: RemovalPolicy.DESTROY,
    });

    const lambdaEnvironment = {
      TableName,
      TableArn: table.tableArn,
      BucketName: brefBucket.bucketName,
    };

    const functionName = 'GetFibonacciImage';
    const getLambda = new PhpFunction(this, `${stackPrefix}${functionName}`, {
      handler: 'get.php',
      phpVersion: '8.3',
      runtime: Runtime.PROVIDED_AL2,
      code: packagePhpCode(join(__dirname, `../assets/get`), {
        exclude: ['test', 'tests'],
      }),
      functionName,
      environment: lambdaEnvironment,
    });

    const fnUrl = getLambda.addFunctionUrl({ authType: FunctionUrlAuthType.NONE });

    brefBucket.grantReadWrite(getLambda);

    table.grantReadWriteData(getLambda);

    new CfnOutput(this, 'TheUrl', {
      // The .url attributes will return the unique Function URL
      value: fnUrl.url,
    });

    new CfnOutput(this, 'Bucket', { value: brefBucket.bucketName });
  }
}
